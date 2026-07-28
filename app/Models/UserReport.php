<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserReport
 *
 * Handles user report operations for the application.
 */
class UserReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'category',
        'subject',
        'description',
        'status',
        'admin_reply'
    ];

    /**
     * User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
