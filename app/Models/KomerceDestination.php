<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class KomerceDestination
 *
 * Handles komerce destination operations for the application.
 */
class KomerceDestination extends Model
{
    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'province_id',
        'province_name',
        'city_id',
        'city_name',
        'district_id',
        'district_name',
        'subdistrict_name',
        'zip_code',
        'label',
        'synced_at',
    ];

    protected $casts = [
        'synced_at' => 'datetime',
    ];
}
