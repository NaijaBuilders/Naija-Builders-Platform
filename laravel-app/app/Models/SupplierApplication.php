<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierApplication extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'DRAFT';

    public const STATUS_SUBMITTED = 'SUBMITTED';

    public const STATUS_VERIFYING = 'VERIFYING';

    public const STATUS_APPROVED = 'APPROVED';

    public const STATUS_REJECTED = 'REJECTED';

    public const STATUS_MANUAL_REVIEW = 'MANUAL_REVIEW';

    public const STATUS_MORE_INFO_REQUIRED = 'MORE_INFO_REQUIRED';

    public const STATUS_SUSPENDED = 'SUSPENDED';

    public const GENERIC_REJECTION_MESSAGE = 'We could not verify your supplier application at this time.';

    protected $fillable = [
        'user_id',
        'status',
        'current_stage',
        'provider',
        'cac_number',
        'cac_number_hash',
        'business_name',
        'business_type',
        'business_address',
        'state',
        'contact_name',
        'contact_email',
        'contact_phone',
        'bvn_hash',
        'bvn_mask',
        'nin_hash',
        'nin_mask',
        'id_document_type',
        'id_document_path',
        'selfie_path',
        'face_match_score',
        'liveness_passed',
        'bank_name',
        'bank_code',
        'account_number_hash',
        'account_number_mask',
        'account_name',
        'name_match_score',
        'supplier_message',
        'more_info_message',
        'submitted_at',
        'decided_at',
    ];

    protected $casts = [
        'face_match_score' => 'integer',
        'liveness_passed' => 'boolean',
        'name_match_score' => 'integer',
        'submitted_at' => 'datetime',
        'decided_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function checks(): HasMany
    {
        return $this->hasMany(VerificationCheck::class);
    }

    public function decisions(): HasMany
    {
        return $this->hasMany(VerificationDecision::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(SupplierAuditLog::class);
    }

    public function riskSignals(): HasMany
    {
        return $this->hasMany(RiskSignal::class);
    }

    public function manualReviewNotes(): HasMany
    {
        return $this->hasMany(ManualReviewNote::class);
    }
}
