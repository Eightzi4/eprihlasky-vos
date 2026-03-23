<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ApplicationRound extends Model
{
    protected $fillable = [
        'study_program_id',
        'academic_year',
        'label',
        'opens_at',
        'closes_at',
        'max_applicants',
        'is_active',
    ];

    protected $casts = [
        'opens_at'  => 'datetime',
        'closes_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function studyProgram()
    {
        return $this->belongsTo(StudyProgram::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class, 'round_id');
    }

    public function isOpen(): bool
    {
        if (! $this->is_active) return false;
        $now = now();
        return $now->greaterThanOrEqualTo($this->opens_at)
            && $now->lessThanOrEqualTo($this->closes_at);
    }

    public function isUpcoming(): bool
    {
        return $this->is_active && now()->lessThan($this->opens_at);
    }

    public function isClosed(): bool
    {
        return ! $this->is_active || now()->greaterThan($this->closes_at);
    }

    public function isFull(): bool
    {
        if (! $this->max_applicants) return false;
        return $this->applications()->count() >= $this->max_applicants;
    }

    public function canAcceptApplications(): bool
    {
        return $this->isOpen() && ! $this->isFull();
    }

    public function displayLabel(): string
    {
        if ($this->label) return $this->label;
        return $this->opens_at->translatedFormat('j. n. Y')
            . ' – '
            . $this->closes_at->translatedFormat('j. n. Y');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where('opens_at', '<=', now())
            ->where('closes_at', '>=', now());
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where('opens_at', '>', now());
    }

    public function scopeForProgram(Builder $query, int $programId): Builder
    {
        return $query->where('study_program_id', $programId);
    }

    public function overlaps(self $other): bool
    {
        return $this->opens_at->lessThanOrEqualTo($other->closes_at)
            && $this->closes_at->greaterThanOrEqualTo($other->opens_at);
    }
}
