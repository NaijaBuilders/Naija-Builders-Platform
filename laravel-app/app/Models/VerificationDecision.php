<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationDecision extends Model
{
    protected $fillable = [
        'supplier_application_id',
        'decision',
        'previous_status',
        'new_status',
        'triggered_checks',
        'internal_reason_codes',
        'reviewer_id',
        'notes',
    ];

    protected $casts = [
        'triggered_checks' => 'array',
        'internal_reason_codes' => 'array',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(SupplierApplication::class, 'supplier_application_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
