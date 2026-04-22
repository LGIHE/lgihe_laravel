<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageLoad extends Model
{
    protected $fillable = [
        'url',
        'load_time',
        'session_id',
        'user_agent',
        'country',
        'country_code',
        'city',
        'timestamp',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];
}
