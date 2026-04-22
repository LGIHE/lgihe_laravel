<?php

namespace App\Models;

use Database\Factories\JobListingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobListing extends Model
{
    /** @use HasFactory<JobListingFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'requirements',
        'responsibilities',
        'location',
        'department',
        'reports_to',
        'supervises_who',
        'employment_type',
        'salary_range',
        'application_deadline',
        'document_path',
        'document_name',
        'document_type',
        'document_size',
        'status',
        'published_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'application_deadline' => 'date',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('application_deadline')
                    ->orWhere('application_deadline', '>=', now());
            });
    }

    /**
     * Get the full URL for the document
     */
    public function getDocumentUrlAttribute(): ?string
    {
        if (!$this->document_path) {
            return null;
        }

        return asset('storage/' . $this->document_path);
    }

    /**
     * Check if the job has a document attached
     */
    public function hasDocument(): bool
    {
        return !empty($this->document_path) && \Storage::disk('public')->exists($this->document_path);
    }

    /**
     * Get formatted file size
     */
    public function getFormattedFileSizeAttribute(): ?string
    {
        if (!$this->document_size) {
            return null;
        }

        $bytes = $this->document_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
