<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationCheck extends Model
{
    protected $fillable = [
        'supplier_application_id',
        'provider',
        'check_type',
        'status',
        'reason_codes',
        'normalized_result',
        'checked_at',
    ];

    protected $casts = [
        'reason_codes' => 'array',
        'normalized_result' => 'array',
        'checked_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(SupplierApplication::class, 'supplier_application_id');
    }
}
