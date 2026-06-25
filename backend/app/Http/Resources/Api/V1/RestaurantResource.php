<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RestaurantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'legal_name' => $this->legal_name,
            'gstin' => $this->gstin,
            'pan' => $this->pan,
            'logo_url' => $this->logo_url ? asset('storage/'.$this->logo_url) : null,
            'email' => $this->email,
            'phone' => $this->phone,
            'address_line1' => $this->address_line1,
            'address_line2' => $this->address_line2,
            'city' => $this->city,
            'state' => $this->state,
            'pincode' => $this->pincode,
            'country' => $this->country,
            'currency_code' => $this->currency_code,
            'timezone' => $this->timezone,
            'default_tax_rate' => $this->default_tax_rate !== null ? (float) $this->default_tax_rate : null,
            'fssai_number' => $this->fssai_number,
            'created_at' => optional($this->created_at)?->toIso8601String(),
        ];
    }
}