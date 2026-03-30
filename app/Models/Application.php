<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $fillable = [
        'user_id',
        'study_program_id',
        'round_id',
        'application_status_id',
        'status_changed_at',
        'status_notified_at',
        'identity_verified',
        'prev_study_info',
        'paid',
        'gdpr_accepted',
        'submitted',
        'submitted_at',
        'education_accepted_at',
        'education_notified_at',
        'payment_accepted_at',
        'payment_notified_at',
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
        'half_year_grade_average',
        'maturita_grade_average',
        'bring_maturita_in_person',
        'specific_needs',
        'note',
        'verified_fields',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'submitted_at' => 'datetime',
        'education_accepted_at' => 'datetime',
        'education_notified_at' => 'datetime',
        'payment_accepted_at' => 'datetime',
        'payment_notified_at' => 'datetime',
        'status_changed_at' => 'datetime',
        'status_notified_at' => 'datetime',
        'identity_verified' => 'boolean',
        'prev_study_info' => 'boolean',
        'paid' => 'boolean',
        'gdpr_accepted' => 'boolean',
        'submitted' => 'boolean',
        'bring_maturita_in_person' => 'boolean',
        'prev_study_info_accepted' => 'boolean',
        'payment_accepted' => 'boolean',
        'verified_fields' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function round()
    {
        return $this->belongsTo(ApplicationRound::class, 'round_id');
    }

    public function applicationStatus()
    {
        return $this->belongsTo(ApplicationStatus::class);
    }

    public function studyProgram()
    {
        return $this->belongsTo(StudyProgram::class);
    }

    public function attachments()
    {
        return $this->hasMany(ApplicationAttachment::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function statusCode(): string
    {
        return $this->applicationStatus?->code ?? ApplicationStatus::DRAFT;
    }

    public function transitionToStatus(string $statusCode, ?CarbonInterface $changedAt = null): void
    {
        $changedAt ??= now();

        $this->forceFill([
            'application_status_id' => ApplicationStatus::idFor($statusCode),
            'status_changed_at' => $changedAt,
            'status_notified_at' => null,
        ])->save();

        $this->unsetRelation('applicationStatus');
        $this->load('applicationStatus');
    }

    public function markStatusNotified(?CarbonInterface $notifiedAt = null): void
    {
        $this->forceFill([
            'status_notified_at' => $notifiedAt ?? now(),
        ])->save();
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
            if (empty($this->{$field})) {
                return false;
            }
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
            'half_year_grade_average',
            'maturita_grade_average',
        ];

        foreach ($required as $field) {
            if (empty($this->{$field})) {
                return false;
            }
        }

        $hasHalfYearReport = $this->hasAttachmentType('half_year_report');
        $hasMaturita = $this->bring_maturita_in_person
            || $this->hasAttachmentType('maturita');

        return $hasHalfYearReport && $hasMaturita;
    }

    public function checkpointStatuses(): array
    {
        return [
            'step1' => $this->step1Status(),
            'identity_verified' => $this->niaStatus(),
            'gdpr_accepted' => $this->gdprStatus(),
            'submitted' => $this->submittedStatus(),
            'step2' => $this->step2Status(),
            'payment' => $this->paymentStatus(),
        ];
    }

    public function applicantCompletionRequirementsMet(): bool
    {
        return $this->isStep1Complete()
            && (bool) $this->gdpr_accepted
            && (bool) $this->submitted
            && $this->isStep2Complete()
            && (bool) $this->paid;
    }

    public function canSubmit(): bool
    {
        return ! $this->submitted
            && ! $this->submissionDeadlinePassed()
            && $this->isStep1Complete()
            && (bool) $this->gdpr_accepted;
    }

    public function isStep1Locked(): bool
    {
        return $this->submissionDeadlinePassed() || (bool) $this->submitted;
    }

    public function isStep2Locked(): bool
    {
        return $this->completionDeadlinePassed();
    }

    public function isStep3Locked(): bool
    {
        return $this->completionDeadlinePassed();
    }

    public function isPaymentSectionLocked(): bool
    {
        return $this->completionDeadlinePassed();
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

    public function submissionDeadlineAt()
    {
        return $this->round?->closes_at;
    }

    public function completionDeadlineAt()
    {
        return $this->round?->completion_deadline_at;
    }

    public function submissionDeadlinePassed(): bool
    {
        return (bool) ($this->submissionDeadlineAt()?->isPast());
    }

    public function completionDeadlinePassed(): bool
    {
        return (bool) ($this->completionDeadlineAt()?->isPast());
    }

    public function isMovedToFurtherRound(): bool
    {
        return $this->statusCode() === ApplicationStatus::MOVED_TO_FURTHER_ROUND;
    }

    public function completionOutcomeCode(): ?string
    {
        if (! $this->completionDeadlinePassed()) {
            return null;
        }

        return $this->applicantCompletionRequirementsMet()
            ? ApplicationStatus::COMPLETED_IN_TIME
            : ApplicationStatus::INCOMPLETE_AFTER_DEADLINE;
    }

    public function statusNotice(): ?array
    {
        return match ($this->completionOutcomeCode()) {
            ApplicationStatus::COMPLETED_IN_TIME => [
                'code' => ApplicationStatus::COMPLETED_IN_TIME,
                'tone' => 'success',
                'icon' => 'verified',
                'title' => 'Přihláška byla dokončena včas',
                'body' => 'Všechny části, které bylo potřeba z Vaší strany doplnit, byly vyplněny do termínu. Škola nyní může dokončit kontrolu dokumentů a platby.',
            ],
            ApplicationStatus::INCOMPLETE_AFTER_DEADLINE => [
                'code' => ApplicationStatus::INCOMPLETE_AFTER_DEADLINE,
                'tone' => 'error',
                'icon' => 'cancel',
                'title' => 'Přihláška nebyla dokončena včas',
                'body' => 'Termín pro doplnění přihlášky už uplynul. Nedokončené části jsou označeny červeně. Pokud Vás škola přesune do dalšího kola, pošleme Vám další informaci e-mailem.',
            ],
            default => null,
        };
    }

    public function statusPanelData(): array
    {
        return [
            's1' => $this->step1Status(),
            's2' => $this->step2Status(),
            'ps' => $this->paymentStatus(),
            'nia' => $this->niaStatus(),
            'gdpr' => $this->gdprStatus(),
            'submitted' => $this->submittedStatus(),
            'canSubmit' => $this->canSubmit(),
            'notice' => $this->statusNotice(),
            'finalStatus' => $this->finalStatusSummary(),
        ];
    }

    public function finalStatusSummary(): ?array
    {
        return match ($this->completionOutcomeCode()) {
            ApplicationStatus::COMPLETED_IN_TIME => [
                'tone' => 'success',
                'icon' => 'verified',
                'label' => 'Přihláška byla dokončena včas',
            ],
            ApplicationStatus::INCOMPLETE_AFTER_DEADLINE => [
                'tone' => 'error',
                'icon' => 'cancel',
                'label' => 'Přihláška nebyla dokončena včas',
            ],
            default => null,
        };
    }

    public function step1Status(): string
    {
        if ($this->shouldFailSection('step1')) {
            return 'failed';
        }

        if ($this->isStep1Complete()) {
            return 'complete';
        }

        return $this->isStep1Locked() ? 'locked' : 'incomplete';
    }

    public function niaStatus(): string
    {
        if ($this->shouldFailSection('nia')) {
            return 'failed';
        }

        if ($this->identity_verified) {
            return 'complete';
        }

        return $this->isStep1Locked() ? 'locked' : 'incomplete';
    }

    public function gdprStatus(): string
    {
        if ($this->shouldFailSection('gdpr')) {
            return 'failed';
        }

        if ($this->gdpr_accepted) {
            return 'complete';
        }

        return $this->isStep1Locked() ? 'locked' : 'incomplete';
    }

    public function submittedStatus(): string
    {
        if ($this->shouldFailSection('submitted')) {
            return 'failed';
        }

        return $this->submitted ? 'complete' : 'incomplete';
    }

    public function step2Status(): string
    {
        if ($this->prev_study_info_accepted) {
            return 'complete';
        }

        if ($this->isStep2Complete()) {
            return 'pending';
        }

        if ($this->shouldFailSection('step2')) {
            return 'failed';
        }

        return $this->isStep2Locked() ? 'locked' : 'incomplete';
    }

    public function paymentStatus(): string
    {
        if ($this->paid && $this->payment_accepted) {
            return 'complete';
        }

        if ($this->paid) {
            return 'pending';
        }

        if ($this->shouldFailSection('payment')) {
            return 'failed';
        }

        return $this->isPaymentSectionLocked() ? 'locked' : 'incomplete';
    }

    public function evaluateStates(): void
    {
        $this->prev_study_info = $this->isStep2Complete();
        $this->paid = $this->hasAttachmentType('payment');
        $this->save();
    }

    private function shouldFailSection(string $section): bool
    {
        if (! $this->completionDeadlinePassed()) {
            return false;
        }

        return match ($section) {
            'step1' => ! $this->isStep1Complete(),
            'nia' => ! $this->identity_verified,
            'gdpr' => ! $this->gdpr_accepted,
            'submitted' => ! $this->submitted,
            'step2' => ! $this->isStep2Complete(),
            'payment' => ! $this->paid,
            default => false,
        };
    }

    private function hasAttachmentType(string $type): bool
    {
        if ($this->relationLoaded('attachments')) {
            return $this->attachments->contains(fn ($attachment) => $attachment->type === $type);
        }

        return $this->attachments()->where('type', $type)->exists();
    }
}
