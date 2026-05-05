<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AbuseReport extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'report_id',
        'reporter_name',
        'reporter_email',
        'reporter_phone',
        'reporter_relationship',
        'preferred_contact',
        'anonymous_report',
        'incident_type',
        'incident_date',
        'incident_location',
        'persons_involved',
        'detailed_description',
        'witnesses_present',
        'previously_reported',
        'evidence_available',
        'status',
        'assigned_to',
        'resolved_at',
    ];

    protected $casts = [
        'incident_date' => 'date',
        'anonymous_report' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    protected $hidden = [
        'reporter_name',
        'reporter_email',
        'reporter_phone',
    ];

    /**
     * Get the user assigned to this report
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Scope for pending reports
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for in progress reports
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope for resolved reports
     */
    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    /**
     * Scope for anonymous reports
     */
    public function scopeAnonymous($query)
    {
        return $query->where('anonymous_report', true);
    }

    /**
     * Generate a unique report ID
     */
    public static function generateReportId(): string
    {
        do {
            $reportId = 'ABR-' . now()->timestamp . '-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 9));
        } while (self::where('report_id', $reportId)->exists());

        return $reportId;
    }

    /**
     * Get incident type display name
     */
    public function getIncidentTypeDisplayAttribute(): string
    {
        return match($this->incident_type) {
            'physical-abuse' => 'Physical Abuse',
            'sexual-harassment' => 'Sexual Harassment',
            'sexual-assault' => 'Sexual Assault',
            'verbal-abuse' => 'Verbal Abuse',
            'bullying' => 'Bullying',
            'discrimination' => 'Discrimination',
            'stalking' => 'Stalking',
            'emotional-abuse' => 'Emotional/Psychological Abuse',
            'financial-exploitation' => 'Financial Exploitation',
            'neglect' => 'Neglect',
            'other' => 'Other',
            default => ucfirst(str_replace('-', ' ', $this->incident_type)),
        };
    }
}
