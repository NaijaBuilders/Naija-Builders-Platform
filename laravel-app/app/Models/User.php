<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'full_name',
        'email',
        'username',
        'phone',
        'company',
        'business_category',
        'location',
        'business_address',
        'business_description',
        'bank_name',
        'account_number',
        'role',
        'offers_services',
        'service_category',
        'service_areas',
        'password_hash',
        'kyc_status',
        'kyc_submitted_at',
        'kyc_verified_at',
        'is_verified_badge',
        'registration_ip_hash',
        'registration_ip_display',
        'device_fingerprint_hash',
        'device_fingerprint_display',
        'email_otp_hash',
        'email_otp_expires_at',
        'email_verified_at',
        'phone_otp_hash',
        'phone_otp_expires_at',
        'phone_verified_at',
        'first_transaction_monitoring',
        'first_successful_order_id',
        'first_transaction_completed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kyc_submitted_at' => 'datetime',
            'kyc_verified_at' => 'datetime',
            'is_verified_badge' => 'boolean',
            'offers_services' => 'boolean',
            'email_otp_expires_at' => 'datetime',
            'email_verified_at' => 'datetime',
            'phone_otp_expires_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'first_transaction_monitoring' => 'boolean',
            'first_transaction_completed_at' => 'datetime',
        ];
    }

    public function getAuthPassword(): string
    {
        return (string) $this->password_hash;
    }
}
