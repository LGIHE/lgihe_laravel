<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsEvent extends Model
{
    protected $fillable = [
        'name',
        'properties',
        'session_id',
        'user_agent',
        'referrer',
        'screen_resolution',
        'country',
        'country_code',
        'city',
        'timestamp',
    ];

    protected $casts = [
        'properties' => 'array',
        'timestamp' => 'datetime',
    ];
}
