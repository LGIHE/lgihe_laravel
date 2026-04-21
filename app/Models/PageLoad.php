<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageLoad extends Model
{
    protected $fillable = [
        'session_id',
        'page_url',
        'page_title',
        'referrer',
        'load_time',
        'device_type',
        'browser',
        'os',
        'user_agent',
        'ip_address',
    ];
}
