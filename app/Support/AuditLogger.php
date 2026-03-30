<?php

namespace App\Support;

use App\Models\Admin;
use App\Models\Application;
use App\Models\AuditActionType;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AuditLogger
{
    public static function log(Request $request, string $actionCode, ?Application $application = null, ?string $description = null): void
    {
        if (! Schema::hasTable('audit_logs') || ! Schema::hasTable('audit_action_types')) {
            return;
        }

        try {
            $admin = Auth::guard('admin')->user();
            $startedAt = self::sessionStartedAt($request);

            AuditLog::query()->create([
                'admin_id' => $admin?->id,
                'action_type_id' => self::actionTypeId($actionCode),
                'application_id' => $application?->id,
                'description' => $description,
                'not_before' => $startedAt,
                'not_after' => $startedAt->copy()->addMinutes((int) config('session.lifetime', 120)),
                'ip_address' => $request->ip(),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Audit logging failed: ' . $e->getMessage(), [
                'action' => $actionCode,
                'application_id' => $application?->id,
            ]);
        }
    }

    public static function rememberSessionStart(Request $request, ?Admin $admin = null): void
    {
        if ($admin || Auth::guard('admin')->check()) {
            $request->session()->put('admin_session_started_at', now()->toIso8601String());
        }
    }

    private static function sessionStartedAt(Request $request)
    {
        $value = $request->session()->get('admin_session_started_at');

        if ($value) {
            return now()->parse($value);
        }

        $now = now();
        $request->session()->put('admin_session_started_at', $now->toIso8601String());

        return $now;
    }

    private static function actionTypeId(string $code): int
    {
        static $cache = [];

        return $cache[$code] ??= (int) AuditActionType::query()
            ->where('code', $code)
            ->value('id');
    }
}
