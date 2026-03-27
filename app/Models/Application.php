<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Application extends Model
{
    protected $fillable = [
        'user_id',
        'study_program_id',
        'round_id',
        'status',
        'identity_verified',
        'prev_study_info',
        'paid',
        'gdpr_accepted',
        'submitted',
        'submitted_at',
        'application_number',
        'evidence_number',
        'prev_study_info_accepted',
        'payment_accepted',
        'first_name',
        'last_name',
        'gender',
        'birth_number',
        'birth_date',
        'birth_city',
        'citizenship',
        'email',
        'phone',
        'street',
        'city',
        'zip',
        'country',
        'previous_school',
        'izo',
        'school_type',
        'previous_study_field',
        'previous_study_field_code',
        'graduation_year',
        'grade_average',
        'specific_needs',
        'note',
        'verified_fields',
        'education_locked_at',
        'payment_locked_at',
        'deadline_at',
    ];

    protected $casts = [
        'birth_date'          => 'date',
        'submitted_at'        => 'datetime',
        'education_locked_at' => 'datetime',
        'payment_locked_at'   => 'datetime',
        'deadline_at'         => 'datetime',
        'identity_verified'   => 'boolean',
        'prev_study_info'     => 'boolean',
        'paid'                => 'boolean',
        'gdpr_accepted'       => 'boolean',
        'submitted'           => 'boolean',
        'prev_study_info_accepted' => 'boolean',
        'payment_accepted'    => 'boolean',
        'verified_fields'     => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function round()
    {
        return $this->belongsTo(ApplicationRound::class, 'round_id');
    }

    public function studyProgram()
    {
        return $this->belongsTo(StudyProgram::class);
    }

    public function attachments()
    {
        return $this->hasMany(ApplicationAttachment::class);
    }

    public function isStep1Complete(): bool
    {
        $required = [
            'first_name',
            'last_name',
            'gender',
            'birth_number',
            'birth_date',
            'birth_city',
            'citizenship',
            'email',
            'phone',
            'street',
            'city',
            'zip',
            'country',
        ];

        foreach ($required as $field) {
            if (empty($this->{$field})) return false;
        }

        return (bool) $this->identity_verified;
    }

    public function isStep2Complete(): bool
    {
        $required = [
            'previous_school',
            'izo',
            'school_type',
            'previous_study_field',
            'previous_study_field_code',
            'graduation_year',
            'grade_average',
        ];

        foreach ($required as $field) {
            if (empty($this->{$field})) return false;
        }

        return $this->attachments()->where('type', 'maturita')->exists();
    }

    public function isStep1Locked(): bool
    {
        if ($this->deadlinePassed()) return true;
        return (bool) $this->submitted;
    }

    public function isStep2Locked(): bool
    {
        if ($this->deadlinePassed()) return true;
        if ($this->education_locked_at && $this->education_locked_at->isPast()) return true;
        return false;
    }

    public function isStep3Locked(): bool
    {
        if ($this->deadlinePassed()) return true;
        return false;
    }

    public function isPaymentSectionLocked(): bool
    {
        if ($this->deadlinePassed()) return true;
        if ($this->payment_locked_at && $this->payment_locked_at->isPast()) return true;
        return false;
    }

    public function isStepLocked(int $step): bool
    {
        return match ($step) {
            1 => $this->isStep1Locked(),
            2 => $this->isStep2Locked(),
            3 => $this->isStep3Locked(),
            default => false,
        };
    }

    public function deadlinePassed(): bool
    {
        return $this->deadline_at && $this->deadline_at->isPast();
    }

    public function step1Status(): string
    {
        if ($this->isStep1Locked()) return 'locked';
        if ($this->isStep1Complete()) return 'complete';
        return 'incomplete';
    }

    public function step2Status(): string
    {
        if ($this->isStep2Locked()) return 'locked';
        if ($this->prev_study_info_accepted) return 'complete';
        if ($this->isStep2Complete()) return 'pending';
        return 'incomplete';
    }

    public function paymentStatus(): string
    {
        if ($this->isPaymentSectionLocked()) return 'locked';
        if ($this->paid && $this->payment_accepted) return 'complete';
        if ($this->paid) return 'pending';
        return 'incomplete';
    }

    public function evaluateStates(): void
    {
        $this->prev_study_info = $this->isStep2Complete();

        $paymentFile = $this->attachments()->where('type', 'payment')->exists();
        $this->paid  = $paymentFile;

        $this->save();
    }
}
