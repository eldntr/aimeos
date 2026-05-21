<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerBankDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bank_account_number',
        'bank_account_name',
        'bank_name',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
