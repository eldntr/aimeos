<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerWithdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'siteid',
        'amount',
        'status',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'notes',
    ];
}
