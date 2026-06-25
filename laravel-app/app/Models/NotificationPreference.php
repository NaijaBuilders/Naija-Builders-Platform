<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'event_key',
        'push',
        'email',
        'sms',
        'in_app',
    ];

    protected $casts = [
        'push' => 'boolean',
        'email' => 'boolean',
        'sms' => 'boolean',
        'in_app' => 'boolean',
    ];
}
