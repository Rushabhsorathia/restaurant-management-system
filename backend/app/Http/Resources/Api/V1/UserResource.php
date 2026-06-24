<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'restaurant_id' => $this->restaurant_id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar_url' => $this->avatar_url,
            'is_active' => (bool) $this->is_active,
            'must_change_password' => (bool) $this->must_change_password,
            'last_login_at' => optional($this->last_login_at)?->toIso8601String(),
            'roles' => $this->whenLoaded('roles', fn () => $this->roles->pluck('name')->values()),
            'permissions' => $this->when(
                $this->resource?->relationLoaded('permissions') || $request->user()?->id === $this->id,
                fn () => $this->getAllPermissions()->pluck('name')->values()
            ),
            'outlets' => OutletResource::collection($this->whenLoaded('outlets')),
            'created_at' => optional($this->created_at)?->toIso8601String(),
        ];
    }
}
