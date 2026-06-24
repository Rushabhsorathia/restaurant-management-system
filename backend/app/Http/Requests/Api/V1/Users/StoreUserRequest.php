<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Users;

use App\Models\Outlet;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('users.create') ?? false;
    }

    public function rules(): array
    {
        $user = $this->user();
        $restaurantId = $user?->restaurant_id;

        $outletIds = Outlet::query()
            ->when($restaurantId && ! $user?->hasRole('hq_admin'), fn ($q) => $q->where('restaurant_id', $restaurantId))
            ->pluck('id')
            ->all();

        return [
            'name' => ['required', 'string', 'max:191'],
            'email' => ['required', 'string', 'email:rfc', 'max:191', Rule::unique('users', 'email')->whereNull('deleted_at')],
            'phone' => ['nullable', 'string', 'max:20'],
            'avatar_url' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'regex:/[A-Za-z]/', 'regex:/\d/'],
            'must_change_password' => ['boolean'],
            'is_active' => ['boolean'],
            'restaurant_id' => ['nullable', 'integer', Rule::exists('restaurants', 'id')],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', Rule::exists('roles', 'name')],
            'outlets' => ['required', 'array', 'min:1'],
            'outlets.*' => ['integer', Rule::in($outletIds)],
        ];
    }

    public function messages(): array
    {
        return [
            'password.regex' => 'Password must contain at least one letter and one number.',
            'roles.required' => 'At least one role must be assigned.',
            'outlets.required' => 'At least one outlet must be assigned.',
        ];
    }
}
