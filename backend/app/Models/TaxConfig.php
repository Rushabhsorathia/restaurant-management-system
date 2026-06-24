<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'name',
        'type',
        'cgst_rate',
        'sgst_rate',
        'igst_rate',
        'cess_rate',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'cgst_rate' => 'decimal:2',
            'sgst_rate' => 'decimal:2',
            'igst_rate' => 'decimal:2',
            'cess_rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }
}
