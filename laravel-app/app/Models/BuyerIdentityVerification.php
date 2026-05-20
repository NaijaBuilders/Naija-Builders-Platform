<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuyerIdentityVerification extends Model
{
    protected $fillable = [
        'user_id',
        'provider',
        'provider_reference',
        'verified_id_name',
        'document_type',
        'status',
        'document_path',
        'selfie_path',
        'face_match_score',
        'liveness_passed',
        'normalized_result',
        'submitted_at',
        'verified_at',
    ];

    protected $casts = [
        'liveness_passed' => 'boolean',
        'normalized_result' => 'array',
        'submitted_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
