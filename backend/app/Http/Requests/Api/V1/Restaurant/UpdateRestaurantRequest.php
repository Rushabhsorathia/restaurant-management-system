<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Restaurant;

use App\Rules\Gstin;
use App\Rules\Pan;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRestaurantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('restaurant.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:191'],
            'legal_name' => ['sometimes', 'required', 'string', 'max:191'],
            'gstin' => ['nullable', 'string', 'max:15', new Gstin()],
            'pan' => ['nullable', 'string', 'max:10', new Pan()],
            'email' => ['sometimes', 'required', 'string', 'email:rfc', 'max:191'],
            'phone' => ['sometimes', 'required', 'string', 'max:20'],
            'address_line1' => ['sometimes', 'required', 'string', 'max:191'],
            'address_line2' => ['nullable', 'string', 'max:191'],
            'city' => ['sometimes', 'required', 'string', 'max:100'],
            'state' => ['sometimes', 'required', 'string', 'max:100'],
            'pincode' => ['sometimes', 'required', 'string', 'max:10'],
            'country' => ['sometimes', 'required', 'string', 'max:50'],
            'currency_code' => ['sometimes', 'required', 'string', 'max:3'],
            'timezone' => ['sometimes', 'required', 'string', 'max:50'],
            'default_tax_rate' => ['nullable', 'numeric', 'between:0,100'],
            'fssai_number' => ['nullable', 'string', 'max:20'],
        ];
    }
}