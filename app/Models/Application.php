<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reference_no',
        'first_name',
        'middle_name',
        'last_name',
        'date_of_birth',
        'gender',
        'nationality',
        'id_number',
        'passport_number',
        'email',
        'phone',
        'alternative_phone',
        'address',
        'city',
        'district',
        'country',
        'programme_choice_1',
        'programme_choice_2',
        'intake_year',
        'study_mode',
        'education_history',
        'employment_history',
        'kin_name',
        'kin_relationship',
        'kin_phone',
        'kin_email',
        'additional_info',
        'documents',
        'status',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'education_history' => 'array',
        'employment_history' => 'array',
        'documents' => 'array',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(ApplicationStatusHistory::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(ApplicationNote::class);
    }

    public static function generateReferenceNumber(): string
    {
        $year = now()->year;
        $lastApplication = static::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $number = $lastApplication ? ((int) substr($lastApplication->reference_no, -6)) + 1 : 1;

        return sprintf('LGI-%d-%06d', $year, $number);
    }
}
