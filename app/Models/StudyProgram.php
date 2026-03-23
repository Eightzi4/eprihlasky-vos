<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudyProgram extends Model
{
    protected $fillable = [
        'name',
        'code',
        'degree',
        'form',
        'length',
        'language',
        'location',
        'tuition_fee',
        'description',
        'image_path',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function applicationRounds()
    {
        return $this->hasMany(ApplicationRound::class);
    }

    public function openRound(): ?ApplicationRound
    {
        return $this->applicationRounds
            ->first(fn($r) => $r->isOpen());
    }

    public function nextRound(): ?ApplicationRound
    {
        return $this->applicationRounds
            ->where('is_active', true)
            ->filter(fn($r) => $r->isUpcoming())
            ->sortBy('opens_at')
            ->first();
    }

    public function lastRound(): ?ApplicationRound
    {
        return $this->applicationRounds
            ->filter(fn($r) => $r->isClosed())
            ->sortByDesc('closes_at')
            ->first();
    }

    public function hasAnyRounds(): bool
    {
        return $this->applicationRounds->isNotEmpty();
    }
}
