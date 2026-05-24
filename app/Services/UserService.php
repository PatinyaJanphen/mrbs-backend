<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserService
{
    use LogsActivity;

    public function list(User $actor, array $filters = []): LengthAwarePaginator
    {
        $query = User::query()
            ->withCount('bookings')
            ->when(!$this->isSuperAdmin($actor), fn($query) => $query->where('company_id', $actor->company_id));

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('department', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if (isset($filters['role']) && $filters['role'] !== '') {
            $query->where('role', (int) $filters['role']);
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query->orderBy('role')->orderBy('name')->paginate($filters['per_page'] ?? 20);
    }

    public function getById(User $actor, int $id): User
    {
        return User::query()
            ->withCount('bookings')
            ->when(!$this->isSuperAdmin($actor), fn($query) => $query->where('company_id', $actor->company_id))
            ->findOrFail($id);
    }

    public function create(User $actor, array $data): User
    {
        $this->ensureRoleCanBeManaged($actor, (int) $data['role']);

        $companyId = $this->isSuperAdmin($actor)
            ? ($data['company_id'] ?? null)
            : $actor->company_id;

        $password = $data['password'] ?? str()->random(16);

        return DB::transaction(function () use ($companyId, $password, $data) {
            $user = User::create([
                'company_id' => $companyId,
                'name' => $data['name'],
                'email' => $data['email'],
                'google_id' => null,
                'password' => Hash::make($password),
                'phone' => $data['phone'] ?? null,
                'department' => $data['department'] ?? null,
                'role' => (int) $data['role'],
                'is_active' => $data['is_active'] ?? true,
                'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($data['name']) . '&background=random',
            ]);

            $this->logActivity('user_created', $user, [
                'role' => $user->role,
                'is_active' => $user->is_active,
            ]);

            return $user->loadCount('bookings');
        });
    }

    public function update(User $actor, int $id, array $data): User
    {
        $user = $this->getById($actor, $id);

        $this->ensureUserCanBeManaged($actor, $user);
        $this->ensureRoleCanBeManaged($actor, (int) $data['role']);

        if ($actor->id === $user->id && isset($data['is_active']) && !$data['is_active']) {
            throw ValidationException::withMessages([
                'is_active' => ['ไม่สามารถปิดการใช้งานบัญชีของตนเองได้'],
            ]);
        }

        return DB::transaction(function () use ($actor, $user, $data) {
            $user->update([
                'company_id' => $this->isSuperAdmin($actor) ? ($data['company_id'] ?? $user->company_id) : $user->company_id,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'department' => $data['department'] ?? null,
                'role' => (int) $data['role'],
                'is_active' => $data['is_active'] ?? $user->is_active,
            ]);

            $this->logActivity('user_updated', $user, [
                'role' => $user->role,
                'is_active' => $user->is_active,
            ]);

            return $user->refresh()->loadCount('bookings');
        });
    }

    public function toggleActive(User $actor, int $id): User
    {
        $user = $this->getById($actor, $id);

        $this->ensureUserCanBeManaged($actor, $user);

        if ($actor->id === $user->id) {
            throw ValidationException::withMessages([
                'is_active' => ['ไม่สามารถปิดการใช้งานบัญชีของตนเองได้'],
            ]);
        }

        return DB::transaction(function () use ($user) {
            $user->update(['is_active' => !$user->is_active]);

            $this->logActivity('user_status_updated', $user, [
                'is_active' => $user->is_active,
            ]);

            return $user->refresh()->loadCount('bookings');
        });
    }

    public function bookings(User $actor, int $id, array $filters = []): LengthAwarePaginator
    {
        $user = $this->getById($actor, $id);

        return Booking::with('resource')
            ->where('company_id', $user->company_id)
            ->where('user_id', $user->id)
            ->latest()
            ->paginate($filters['per_page'] ?? 10);
    }

    private function ensureUserCanBeManaged(User $actor, User $target): void
    {
        if (!$this->isSuperAdmin($actor) && $target->role === User::ROLE_SUPER_ADMIN) {
            throw ValidationException::withMessages([
                'role' => ['ไม่มีสิทธิ์จัดการผู้ดูแลระบบสูงสุด'],
            ]);
        }
    }

    private function ensureRoleCanBeManaged(User $actor, int $role): void
    {
        if (!$this->isSuperAdmin($actor) && $role === User::ROLE_SUPER_ADMIN) {
            throw ValidationException::withMessages([
                'role' => ['ไม่มีสิทธิ์กำหนดบทบาทผู้ดูแลระบบสูงสุด'],
            ]);
        }
    }

    private function isSuperAdmin(User $actor): bool
    {
        return (int) $actor->role === User::ROLE_SUPER_ADMIN;
    }
}
