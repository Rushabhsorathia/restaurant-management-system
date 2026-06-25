<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\TaxConfigs;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaxConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('tax.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'type' => ['sometimes', 'required', 'string', Rule::in(['intra_state', 'inter_state'])],
            'cgst_rate' => ['sometimes', 'required', 'numeric', 'between:0,100'],
            'sgst_rate' => ['sometimes', 'required', 'numeric', 'between:0,100'],
            'igst_rate' => ['sometimes', 'required', 'numeric', 'between:0,100'],
            'cess_rate' => ['nullable', 'numeric', 'between:0,100'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}