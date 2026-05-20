<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuyerFraudEvent extends Model
{
    protected $fillable = [
        'user_id',
        'order_id',
        'tier',
        'signal_type',
        'trigger_level',
        'score',
        'signal_hash',
        'signal_display',
        'metadata',
        'captured_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'captured_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
