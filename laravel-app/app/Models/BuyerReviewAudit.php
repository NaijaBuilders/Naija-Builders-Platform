<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuyerReviewAudit extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'reviewer_id',
        'operator_name',
        'action',
        'previous_status',
        'new_status',
        'trigger_level',
        'reason_codes',
        'notes',
        'buyer_message',
        'deadline_at',
    ];

    protected $casts = [
        'reason_codes' => 'array',
        'deadline_at' => 'datetime',
    ];

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
