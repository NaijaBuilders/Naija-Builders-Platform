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
        'phone',
        'company',
        'business_category',
        'location',
        'business_address',
        'business_description',
        'bank_name',
        'account_number',
        'role',
        'password_hash',
        'kyc_status',
        'kyc_submitted_at',
        'kyc_verified_at',
        'is_verified_badge',
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
        ];
    }

    public function getAuthPassword(): string
    {
        return (string) $this->password_hash;
    }
}
