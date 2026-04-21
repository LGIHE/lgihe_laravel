<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsError extends Model
{
    protected $fillable = [
        'session_id',
        'error_type',
        'error_message',
        'stack_trace',
        'page_url',
        'user_agent',
        'ip_address',
    ];
}
