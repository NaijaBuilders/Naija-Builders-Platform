<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayoutAccount extends Model
{
    protected $fillable = [
        'user_id',
        'bank_name',
        'bank_code',
        'account_number_masked',
        'account_name',
        'verified',
        'is_default',
    ];

    protected $casts = [
        'verified' => 'boolean',
        'is_default' => 'boolean',
    ];
}
