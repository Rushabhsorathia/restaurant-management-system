<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Outlet extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'name',
        'code',
        'type',
        'gstin',
        'phone',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'pincode',
        'is_central_kitchen',
        'is_active',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'is_central_kitchen' => 'boolean',
            'is_active' => 'boolean',
            'settings' => 'array',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_outlets')
            ->withTimestamps();
    }

    public function settings(): HasMany
    {
        return $this->hasMany(AppSetting::class);
    }
}
