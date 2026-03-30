<?php

use App\Models\AuditLog;
use App\Support\ApplicationStatusManager;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('audit-logs:prune', function () {
    $deleted = DB::table('audit_logs')
        ->where('created_at', '<', now()->subYears(AuditLog::RETENTION_YEARS))
        ->delete();

    $this->info("Deleted {$deleted} expired audit log entries.");
})->purpose('Delete expired GDPR audit log entries');

Artisan::command('applications:finalize-completion-statuses', function () {
    $processed = app(ApplicationStatusManager::class)->finalizeDueApplications();

    $this->info("Processed {$processed} application completion statuses.");
})->purpose('Finalize application completion statuses after round deadlines');

Schedule::command('audit-logs:prune')->daily();
Schedule::command('applications:finalize-completion-statuses')->everyMinute();
