<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $table = 'addresses';

    protected $fillable = [
        'owner_type',
        'owner_id',
        'label',
        'contact_name',
        'contact_phone',
        'state',
        'lga',
        'address',
        'instructions',
        'default_window',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];
}
