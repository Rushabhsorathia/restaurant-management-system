<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Outlets;

use App\Rules\Gstin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOutletRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('outlets.create') ?? false;
    }

    public function rules(): array
    {
        $restaurantId = $this->user()?->restaurant_id;

        return [
            'name' => ['required', 'string', 'max:191'],
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('outlets', 'code')->where('restaurant_id', $restaurantId),
            ],
            'type' => ['required', 'string', Rule::in(['dine_in', 'qsr', 'cloud_kitchen', 'food_court'])],
            'gstin' => ['nullable', 'string', 'max:15', new Gstin()],
            'phone' => ['nullable', 'string', 'max:20'],
            'address_line1' => ['required', 'string', 'max:191'],
            'address_line2' => ['nullable', 'string', 'max:191'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'pincode' => ['required', 'string', 'max:10'],
            'is_central_kitchen' => ['boolean'],
            'is_active' => ['boolean'],
            'settings' => ['nullable', 'array'],
        ];
    }
}