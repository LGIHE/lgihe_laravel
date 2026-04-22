<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsError extends Model
{
    protected $fillable = [
        'message',
        'stack',
        'url',
        'user_agent',
        'severity',
        'timestamp',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];
}
