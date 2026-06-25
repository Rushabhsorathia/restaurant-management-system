<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaxConfigResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'restaurant_id' => $this->restaurant_id,
            'name' => $this->name,
            'type' => $this->type,
            'cgst_rate' => (float) $this->cgst_rate,
            'sgst_rate' => (float) $this->sgst_rate,
            'igst_rate' => (float) $this->igst_rate,
            'cess_rate' => (float) $this->cess_rate,
            'is_active' => (bool) $this->is_active,
            'created_at' => optional($this->created_at)?->toIso8601String(),
        ];
    }
}