<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'owner_type',
        'owner_id',
        'type',
        'label',
        'brand',
        'last4',
        'token',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    protected $hidden = [
        'token',
    ];
}
