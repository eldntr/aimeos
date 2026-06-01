<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'siteid',
        'telephone',
        'address1',
        'ktp_url',
        'seller_status',
        'rejection_reason',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Check if the user has a given role.
     */
    public function hasRole(string $role): bool
    {
        if (! empty($this->role)) {
            return $this->role === $role;
        }

        if (method_exists($this, 'roles')) {
            try {
                return $this->roles->contains('slug', $role) || $this->roles->contains('name', $role);
            } catch (\Exception $e) {
                return false;
            }
        }

        return false;
    }

    /**
     * Get the bank details associated with the seller.
     */
    public function bankDetail()
    {
        return $this->hasOne(SellerBankDetail::class);
    }
}
