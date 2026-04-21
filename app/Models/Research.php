<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Research extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'abstract',
        'content',
        'authors',
        'category',
        'publication_type',
        'publication_date',
        'document_url',
        'doi',
        'status',
        'published_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'authors' => 'array',
        'publication_date' => 'date',
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

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }
}
