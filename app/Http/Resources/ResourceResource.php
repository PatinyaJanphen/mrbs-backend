<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResourceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'name' => $this->name,
            'type' => $this->type,
            'capacity' => $this->capacity,
            'location' => $this->location,
            'description' => $this->description,
            'status' => $this->status,
            'requires_approval' => (bool) $this->requires_approval,
            'amenities' => $this->amenities,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
