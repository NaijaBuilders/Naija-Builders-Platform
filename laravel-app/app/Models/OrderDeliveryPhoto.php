<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDeliveryPhoto extends Model
{
    protected $fillable = [
        'order_id',
        'uploaded_by',
        'path',
        'captured_at',
        'gps_lat',
        'gps_lng',
        'metadata',
    ];

    protected $casts = [
        'captured_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
