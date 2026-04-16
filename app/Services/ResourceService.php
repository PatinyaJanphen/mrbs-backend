<?php

namespace App\Services;

use App\Models\Resource;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ResourceService
{
    public function list(int $companyId, array $filters = []): LengthAwarePaginator
    {
        $query = Resource::where('company_id', $companyId);

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('equipment', 'like', "%{$search}%");
            });
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', (bool)$filters['is_active']);
        }

        return $query->paginate($filters['per_page'] ?? 20);
    }

    public function create(int $companyId, array $data): Resource
    {
        return Resource::create(array_merge($data, [
            'company_id' => $companyId,
            'is_active' => true,
        ]));
    }
    
    public function getById(int $companyId, int $id): Resource
    {
        return Resource::where('company_id', $companyId)->findOrFail($id);
    }

    public function update(int $companyId, int $id, array $data): Resource
    {
        $resource = $this->getById($companyId, $id);
        $resource->update($data);
        return $resource;
    }
}
