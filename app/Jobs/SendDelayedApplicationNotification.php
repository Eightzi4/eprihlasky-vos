<?php

namespace App\Jobs;

use App\Mail\StyledNotificationMail;
use App\Models\Application;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendDelayedApplicationNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $applicationId,
        public string $type,
        public string $expectedAcceptedAt,
    ) {}

    public function handle(): void
    {
        $application = Application::with(['user', 'studyProgram'])->find($this->applicationId);

        if (! $application || ! $application->user?->email) {
            return;
        }

        if ($this->type === 'education') {
            $acceptedAt = $application->education_accepted_at;
            $alreadyNotified = $application->education_notified_at;
            $isAccepted = (bool) $application->prev_study_info_accepted;
            $subject = 'Přijetí vzdělání bylo potvrzeno';
            $headline = 'Vzdělání bylo uznáno';
            $lines = [
                'Vaše předchozí vzdělání bylo školou úspěšně zkontrolováno a uznáno.',
                'Přihláška k programu ' . ($application->studyProgram?->name ?? 'VOŠ OAUH') . ' pokračuje dalším zpracováním.',
            ];
            $notifiedColumn = 'education_notified_at';
        } elseif ($this->type === 'payment') {
            $acceptedAt = $application->payment_accepted_at;
            $alreadyNotified = $application->payment_notified_at;
            $isAccepted = (bool) $application->payment_accepted;
            $subject = 'Platba přihlášky byla potvrzena';
            $headline = 'Platba byla potvrzena';
            $lines = [
                'Škola ověřila platbu Vaší přihlášky.',
                'Přihláška k programu ' . ($application->studyProgram?->name ?? 'VOŠ OAUH') . ' je díky tomu v této části v pořádku.',
            ];
            $notifiedColumn = 'payment_notified_at';
        } else {
            return;
        }

        if (! $isAccepted || $alreadyNotified || ! $acceptedAt) {
            return;
        }

        if ($acceptedAt->toIso8601String() !== $this->expectedAcceptedAt) {
            return;
        }

        try {
            Mail::to($application->user->email)->send(new StyledNotificationMail(
                subjectLine: $subject,
                headline: $headline,
                lines: $lines,
                buttonLabel: 'Zobrazit přihlášku',
                buttonUrl: route('dashboard'),
                metaLine: 'Číslo přihlášky: ' . ($application->application_number ?: $application->evidence_number ?: '#' . $application->id),
                fallbackUrl: route('dashboard'),
            ));

            $application->forceFill([
                $notifiedColumn => now(),
            ])->save();
        } catch (\Throwable $e) {
            Log::error('Delayed application notification failed: ' . $e->getMessage(), [
                'application_id' => $this->applicationId,
                'type' => $this->type,
            ]);
        }
    }
}
