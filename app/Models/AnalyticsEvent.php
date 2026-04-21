<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsEvent extends Model
{
    protected $fillable = [
        'session_id',
        'event_type',
        'event_name',
        'event_data',
        'page_url',
        'referrer',
        'user_agent',
        'ip_address',
    ];

    protected $casts = [
        'event_data' => 'array',
    ];
}
