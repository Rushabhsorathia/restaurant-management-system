<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Users;

use App\Models\Outlet;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $target = $this->route('user');

        return $user ? $user->can('update', $target) : false;
    }

    public function rules(): array
    {
        $actor = $this->user();
        $target = $this->route('user');
        $restaurantId = $actor?->restaurant_id;

        $outletIds = Outlet::query()
            ->when($restaurantId && ! $actor?->hasRole('hq_admin'), fn ($q) => $q->where('restaurant_id', $restaurantId))
            ->pluck('id')
            ->all();

        return [
            'name' => ['sometimes', 'required', 'string', 'max:191'],
            'email' => [
                'sometimes',
                'required',
                'string',
                'email:rfc',
                'max:191',
                Rule::unique('users', 'email')
                    ->ignore($target?->id)
                    ->whereNull('deleted_at'),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'avatar_url' => ['nullable', 'string', 'max:255'],
            'password' => ['sometimes', 'nullable', 'string', 'min:8', 'regex:/[A-Za-z]/', 'regex:/\d/'],
            'must_change_password' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'restaurant_id' => ['sometimes', 'nullable', 'integer', Rule::exists('restaurants', 'id')],
            'roles' => ['sometimes', 'array', 'min:1'],
            'roles.*' => ['string', Rule::exists('roles', 'name')],
            'outlets' => ['sometimes', 'array', 'min:1'],
            'outlets.*' => ['integer', Rule::in($outletIds)],
        ];
    }
}
