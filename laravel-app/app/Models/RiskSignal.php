<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiskSignal extends Model
{
    protected $fillable = [
        'supplier_application_id',
        'user_id',
        'signal_type',
        'signal_hash',
        'signal_display',
        'metadata',
        'captured_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'captured_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(SupplierApplication::class, 'supplier_application_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
