<?php

namespace App\Models;

use Database\Factories\TenderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tender extends Model
{
    /** @use HasFactory<TenderFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'reference_number',
        'description',
        'requirements',
        'category',
        'closing_date',
        'rfp_document_path',
        'rfp_document_name',
        'rfp_document_type',
        'rfp_document_size',
        'tor_document_path',
        'tor_document_name',
        'tor_document_type',
        'tor_document_size',
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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tender) {
            // If status is open and no published_at is set, set it to now
            if ($tender->status === 'open' && empty($tender->published_at)) {
                $tender->published_at = now();
            }
        });

        static::updating(function ($tender) {
            // If status is changed to open and no published_at is set, set it to now
            if ($tender->status === 'open' && empty($tender->published_at)) {
                $tender->published_at = now();
            }
        });

        static::deleting(function ($tender) {
            // Delete RFP document if it exists
            if ($tender->rfp_document_path && \Storage::disk('public')->exists($tender->rfp_document_path)) {
                \Storage::disk('public')->delete($tender->rfp_document_path);
            }

            // Delete ToR document if it exists
            if ($tender->tor_document_path && \Storage::disk('public')->exists($tender->tor_document_path)) {
                \Storage::disk('public')->delete($tender->tor_document_path);
            }
        });

        static::forceDeleting(function ($tender) {
            // Delete RFP document if it exists (for force delete)
            if ($tender->rfp_document_path && \Storage::disk('public')->exists($tender->rfp_document_path)) {
                \Storage::disk('public')->delete($tender->rfp_document_path);
            }

            // Delete ToR document if it exists (for force delete)
            if ($tender->tor_document_path && \Storage::disk('public')->exists($tender->tor_document_path)) {
                \Storage::disk('public')->delete($tender->tor_document_path);
            }
        });
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where('closing_date', '>=', now()->toDateString());
    }

    /**
     * Get the full URL for the RFP document
     */
    public function getRfpDocumentUrlAttribute(): ?string
    {
        if (!$this->rfp_document_path) {
            return null;
        }

        return asset('storage/' . $this->rfp_document_path);
    }

    /**
     * Get the full URL for the ToR document
     */
    public function getTorDocumentUrlAttribute(): ?string
    {
        if (!$this->tor_document_path) {
            return null;
        }

        return asset('storage/' . $this->tor_document_path);
    }

    /**
     * Check if the tender has an RFP document attached
     */
    public function hasRfpDocument(): bool
    {
        return !empty($this->rfp_document_path) && \Storage::disk('public')->exists($this->rfp_document_path);
    }

    /**
     * Check if the tender has a ToR document attached
     */
    public function hasTorDocument(): bool
    {
        return !empty($this->tor_document_path) && \Storage::disk('public')->exists($this->tor_document_path);
    }

    /**
     * Get formatted RFP file size
     */
    public function getFormattedRfpFileSizeAttribute(): ?string
    {
        if (!$this->rfp_document_size) {
            return null;
        }

        return $this->formatFileSize($this->rfp_document_size);
    }

    /**
     * Get formatted ToR file size
     */
    public function getFormattedTorFileSizeAttribute(): ?string
    {
        if (!$this->tor_document_size) {
            return null;
        }

        return $this->formatFileSize($this->tor_document_size);
    }

    /**
     * Format file size in human-readable format
     */
    private function formatFileSize($bytes): string
    {
        if ($bytes == 0) {
            return '0 B';
        }
        
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
