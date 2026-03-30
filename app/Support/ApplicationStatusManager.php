<?php

namespace App\Support;

use App\Mail\StyledNotificationMail;
use App\Models\Application;
use App\Models\ApplicationRound;
use App\Models\ApplicationStatus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ApplicationStatusManager
{
    public function finalizeDueApplications(): int
    {
        $processed = 0;

        Application::query()
            ->with(['applicationStatus', 'round', 'studyProgram', 'user', 'attachments'])
            ->whereNotNull('round_id')
            ->whereHas('round', fn($query) => $query->where('completion_deadline_at', '<=', now()))
            ->chunkById(100, function ($applications) use (&$processed) {
                foreach ($applications as $application) {
                    $targetStatus = $application->applicantCompletionRequirementsMet()
                        ? ApplicationStatus::COMPLETED_IN_TIME
                        : ApplicationStatus::INCOMPLETE_AFTER_DEADLINE;

                    if ($this->transitionTo($application, $targetStatus, true)) {
                        $processed++;
                    }
                }
            });

        return $processed;
    }

    public function moveToFurtherRound(Application $application, ApplicationRound $round): void
    {
        $application->forceFill([
            'round_id' => $round->id,
        ])->save();

        $application->unsetRelation('round');
        $application->load(['round', 'studyProgram', 'user', 'applicationStatus']);

        $this->transitionTo($application, ApplicationStatus::MOVED_TO_FURTHER_ROUND, true);
    }

    public function transitionTo(Application $application, string $statusCode, bool $notifyApplicant = false): bool
    {
        $changed = false;

        if ($application->statusCode() !== $statusCode) {
            $application->transitionToStatus($statusCode);
            $changed = true;
        }

        if (! $notifyApplicant) {
            return $changed;
        }

        if ($application->status_notified_at) {
            return $changed;
        }

        if ($this->sendApplicantNotification($application, $statusCode)) {
            $application->markStatusNotified();
            return true;
        }

        return $changed;
    }

    private function sendApplicantNotification(Application $application, string $statusCode): bool
    {
        if (! $application->user?->email) {
            return false;
        }

        [$subject, $headline, $lines, $metaLine] = match ($statusCode) {
            ApplicationStatus::COMPLETED_IN_TIME => [
                'Přihláška byla dokončena včas',
                'Přihláška byla dokončena včas',
                [
                    'Vaše přihláška byla do termínu doplněna ve všech částech, které jsou na straně uchazeče povinné.',
                    'Škola nyní může dokončit kontrolu dokumentů a platby k programu ' . ($application->studyProgram?->name ?? 'VOŠ OAUH') . '.',
                ],
                'Termín doplnění: ' . ($application->completionDeadlineAt()?->format('j. n. Y H:i') ?? '—'),
            ],
            ApplicationStatus::INCOMPLETE_AFTER_DEADLINE => [
                'Přihláška nebyla dokončena včas',
                'Přihláška nebyla dokončena včas',
                [
                    'U Vaší přihlášky uplynul termín pro doplnění povinných částí a některé z nich zůstaly nedokončené.',
                    'Pokud Vás škola přesune do dalšího kola, pošleme Vám o tom další informaci.',
                ],
                'Uzávěrka doplnění: ' . ($application->completionDeadlineAt()?->format('j. n. Y H:i') ?? '—'),
            ],
            ApplicationStatus::MOVED_TO_FURTHER_ROUND => [
                'Přihláška byla přesunuta do dalšího kola',
                'Přihláška byla přesunuta do dalšího kola',
                [
                    'Škola přesunula Vaši přihlášku do dalšího přijímacího kola.',
                    'Chybějící části můžete doplnit do ' . ($application->completionDeadlineAt()?->format('j. n. Y H:i') ?? 'nového termínu') . '.',
                ],
                'Nové kolo: ' . ($application->round?->label ?: ($application->round?->academic_year ?? '—')),
            ],
            default => [null, null, [], null],
        };

        if (! $subject || ! $headline) {
            return false;
        }

        try {
            Mail::to($application->user->email)->send(new StyledNotificationMail(
                subjectLine: $subject,
                headline: $headline,
                lines: $lines,
                buttonLabel: 'Zobrazit přihlášku',
                buttonUrl: route('dashboard'),
                metaLine: 'Číslo přihlášky: ' . ($application->application_number ?: $application->evidence_number ?: '#' . $application->id) . ($metaLine ? ' • ' . $metaLine : ''),
                fallbackUrl: route('dashboard'),
            ));

            return true;
        } catch (\Throwable $e) {
            Log::error('Application lifecycle notification failed: ' . $e->getMessage(), [
                'application_id' => $application->id,
                'status' => $statusCode,
            ]);

            return false;
        }
    }
}
