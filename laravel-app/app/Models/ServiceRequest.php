<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'service_type',
        'project_title',
        'project_location',
        'project_description',
        'budget_range',
        'preferred_start_date',
        'contact_name',
        'contact_phone',
        'contact_email',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'preferred_start_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
