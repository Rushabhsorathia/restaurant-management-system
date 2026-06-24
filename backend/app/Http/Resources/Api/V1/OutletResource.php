<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OutletResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'restaurant_id' => $this->restaurant_id,
            'name' => $this->name,
            'code' => $this->code,
            'type' => $this->type,
            'city' => $this->city,
            'is_active' => (bool) $this->is_active,
            'is_central_kitchen' => (bool) $this->is_central_kitchen,
        ];
    }
}
