<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserOutlet extends Model
{
    protected $fillable = [
        'user_id',
        'outlet_id',
    ];

    public $incrementing = false;

    public $timestamps = true;
}
