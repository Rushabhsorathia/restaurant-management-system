<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FullOutletResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'restaurant_id' => $this->restaurant_id,
            'name' => $this->name,
            'code' => $this->code,
            'type' => $this->type,
            'gstin' => $this->gstin,
            'phone' => $this->phone,
            'address_line1' => $this->address_line1,
            'address_line2' => $this->address_line2,
            'city' => $this->city,
            'state' => $this->state,
            'pincode' => $this->pincode,
            'is_central_kitchen' => (bool) $this->is_central_kitchen,
            'is_active' => (bool) $this->is_active,
            'settings' => $this->settings,
            'created_at' => optional($this->created_at)?->toIso8601String(),
        ];
    }
}