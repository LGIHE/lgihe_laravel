<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tender extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'reference_number',
        'description',
        'requirements',
        'category',
        'closing_date',
        'document_url',
        'status',
        'published_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'closing_date' => 'date',
        'published_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where('closing_date', '>=', now());
    }
}
