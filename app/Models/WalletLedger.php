<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class WalletLedger
 *
 * Handles wallet ledger operations for the application.
 */
class WalletLedger extends Model
{
    protected $fillable = [
        'user_id',
        'order_id',
        'type',
        'amount',
        'balance_after',
        'description',
        'reference_number',
        'status'
    ];

    /**
     * Get the user who owns the mutation
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
