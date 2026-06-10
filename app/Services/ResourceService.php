<?php

namespace App\Services;

use App\Models\Resource;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Traits\LogsActivity;

class ResourceService
{
    use LogsActivity;

    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = Resource::query();

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('equipment', 'like', "%{$search}%");
            });
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        return $query->paginate($filters['per_page'] ?? 20);
    }

    public function create(array $data): Resource
    {
        $resource = Resource::create(array_merge($data, [
            'is_active' => true,
        ]));

        $this->logActivity('resource_created', $resource, $data);

        return $resource;
    }

    public function getById(int $id): Resource
    {
        return Resource::findOrFail($id);
    }

    public function update(int $id, array $data): Resource
    {
        $resource = $this->getById($id);
        $resource->update($data);

        $this->logActivity('resource_updated', $resource, $data);

        return $resource;
    }
}
