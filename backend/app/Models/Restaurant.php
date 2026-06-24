<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Restaurant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'legal_name',
        'gstin',
        'pan',
        'logo_url',
        'email',
        'phone',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'pincode',
        'country',
        'currency_code',
        'timezone',
        'default_tax_rate',
        'fssai_number',
    ];

    protected function casts(): array
    {
        return [
            'default_tax_rate' => 'decimal:2',
        ];
    }

    public function outlets(): HasMany
    {
        return $this->hasMany(Outlet::class);
    }

    public function taxConfigs(): HasMany
    {
        return $this->hasMany(TaxConfig::class);
    }

    public function discountConfigs(): HasMany
    {
        return $this->hasMany(DiscountConfig::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function settings(): HasMany
    {
        return $this->hasMany(AppSetting::class);
    }
}
