<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SellerBankDetail
 *
 * Handles seller bank detail operations for the application.
 */
class SellerBankDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bank_account_number',
        'bank_account_name',
        'bank_name',
    ];

    /**
     * User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
