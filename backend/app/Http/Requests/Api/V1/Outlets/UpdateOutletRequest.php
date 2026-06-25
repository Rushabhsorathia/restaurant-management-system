<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Outlets;

use App\Rules\Gstin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOutletRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('outlets.update') ?? false;
    }

    public function rules(): array
    {
        $restaurantId = $this->user()?->restaurant_id;
        $outletId = $this->route('outlet')?->id;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:191'],
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:20',
                Rule::unique('outlets', 'code')
                    ->where('restaurant_id', $restaurantId)
                    ->ignore($outletId),
            ],
            'type' => ['sometimes', 'required', 'string', Rule::in(['dine_in', 'qsr', 'cloud_kitchen', 'food_court'])],
            'gstin' => ['nullable', 'string', 'max:15', new Gstin()],
            'phone' => ['nullable', 'string', 'max:20'],
            'address_line1' => ['sometimes', 'required', 'string', 'max:191'],
            'address_line2' => ['nullable', 'string', 'max:191'],
            'city' => ['sometimes', 'required', 'string', 'max:100'],
            'state' => ['sometimes', 'required', 'string', 'max:100'],
            'pincode' => ['sometimes', 'required', 'string', 'max:10'],
            'is_central_kitchen' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'settings' => ['nullable', 'array'],
        ];
    }
}