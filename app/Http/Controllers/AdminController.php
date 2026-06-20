<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Mail\AdminLoginLinkMail;
use App\Jobs\SendDelayedApplicationNotification;
use App\Models\Admin;
use App\Models\AdminLoginTicket;
use App\Models\Application;
use App\Models\ApplicationAttachment;
use App\Models\ApplicationRound;
use App\Models\AuditActionType;
use App\Models\AuditLog;
use App\Models\StudyProgram;
use App\Models\WebsiteSetting;
use App\Services\TotpService;
use App\Support\ApplicationStatusManager;
use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    private function guard()
    {
        return Auth::guard('admin');
    }


    public function showLogin()
    {
        if ($this->guard()->check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    }

    public function handleEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $admin = Admin::where('email', $request->email)->first();

        if (! $admin) {
            return back()->withErrors([
                'email' => 'K této e-mailové adrese neexistuje žádný administrátorský účet.',
            ])->withInput();
        }

        if ($admin->password) {
            return view('admin.login-password', ['email' => $request->email]);
        }

        $this->sendLoginTicket($admin);
        return view('admin.login-check-email', ['email' => $request->email]);
    }

    public function loginWithPassword(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if ($this->guard()->attempt([
            'email'    => $request->email,
            'password' => $request->password,
        ])) {
            $admin = $this->guard()->user();

            if ($admin->hasTwoFactorEnabled()) {
                $request->session()->put('admin.two_factor.id', $admin->id);
                $request->session()->put('admin.two_factor.remember', $request->boolean('remember'));
                $this->guard()->logout();
                return redirect()->route('admin.login.two-factor');
            }

            $request->session()->regenerate();
            AuditLogger::rememberSessionStart($request, $admin);
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login-password', ['email' => $request->email])
            ->withErrors(['password' => 'Zadané heslo není správné.']);
    }

    public function sendLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $admin = Admin::where('email', $request->email)->firstOrFail();
        $this->sendLoginTicket($admin);

        return view('admin.login-check-email', ['email' => $admin->email]);
    }

    public function verifyTicket(Request $request)
    {
        $token = $request->query('adminTicket');

        if (! $token) {
            return redirect()->route('admin.login')->with('error', 'Chybějící přihlašovací token.');
        }

        $ticket = AdminLoginTicket::where('token', $token)
            ->where('expires_at', '>', now())
            ->whereNull('used_at')
            ->first();

        if (! $ticket) {
            return redirect()->route('admin.login')->with('error', 'Neplatný nebo expirovaný odkaz.');
        }

        $ticket->update(['used_at' => now()]);

        if (! $ticket->admin->password) {
            $this->guard()->login($ticket->admin);
            $request->session()->regenerate();
            AuditLogger::rememberSessionStart($request, $ticket->admin);
            return redirect()->route('admin.setup');
        }

        $this->guard()->login($ticket->admin);
        $request->session()->regenerate();
        AuditLogger::rememberSessionStart($request, $ticket->admin);

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        $this->guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }

    public function showTwoFactorChallenge(Request $request)
    {
        if (! $request->session()->has('admin.two_factor.id')) {
            return redirect()->route('admin.login');
        }

        return view('admin.login-two-factor');
    }

    public function verifyTwoFactor(Request $request, TotpService $totp)
    {
        $adminId = $request->session()->get('admin.two_factor.id');

        if (! $adminId) {
            return redirect()->route('admin.login');
        }

        $admin = Admin::findOrFail($adminId);

        $request->validate([
            'code' => 'required|string',
        ]);

        $code = $request->input('code');

        $attemptsKey = 'two_factor_attempts:' . $admin->id;
        $attempts = (int) cache()->get($attemptsKey, 0);

        if ($attempts >= 5) {
            $request->session()->forget(['admin.two_factor.id', 'admin.two_factor.remember']);
            return redirect()->route('admin.login')->with('error', 'Příliš mnoho pokusů. Zkuste to znovu později.');
        }

        if ($totp->verifyRecoveryCode($admin, $code) !== false) {
            $remaining = $totp->verifyRecoveryCode($admin, $code);
            $admin->setRecoveryCodes($remaining);
            $admin->save();
            return $this->completeTwoFactorLogin($request, $admin);
        }

        if (! $admin->hasTwoFactorEnabled() || ! $totp->verify($admin->two_factor_secret, $code)) {
            cache()->put($attemptsKey, $attempts + 1, now()->addMinutes(5));
            $remaining = 5 - ($attempts + 1);
            return back()->withErrors([
                'code' => $remaining > 0
                    ? "Neplatný kód. Zbývající pokusy: {$remaining}"
                    : 'Příliš mnoho pokusů. Zkuste to znovu později.',
            ]);
        }

        return $this->completeTwoFactorLogin($request, $admin);
    }

    private function completeTwoFactorLogin(Request $request, Admin $admin)
    {
        $request->session()->forget(['admin.two_factor.id', 'admin.two_factor.remember']);

        $this->guard()->login($admin, $request->session()->get('admin.two_factor.remember', false));
        $request->session()->regenerate();
        AuditLogger::rememberSessionStart($request, $admin);

        return redirect()->route('admin.dashboard');
    }

    public function showSetup(Request $request, TotpService $totp)
    {
        $admin = $this->guard()->user();

        if (! $admin) {
            return redirect()->route('admin.login');
        }

        if ($admin->password && $admin->hasTwoFactorEnabled()) {
            return redirect()->route('admin.dashboard');
        }

        if (! $request->session()->has('admin.setup.two_factor_secret')) {
            $secret = $totp->generateSecret();
            $request->session()->put('admin.setup.two_factor_secret', $secret);
        } else {
            $secret = $request->session()->get('admin.setup.two_factor_secret');
        }

        $issuer = config('app.name', 'E-prihlaska');
        $qrUrl = $totp->generateQrCodeUrl($secret, $admin->email, $issuer);
        $qrImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($qrUrl);

        return view('admin.setup', compact('qrUrl', 'qrImageUrl', 'secret'));
    }

    public function storeSetup(Request $request, TotpService $totp)
    {
        $admin = $this->guard()->user();

        if (! $admin) {
            return redirect()->route('admin.login');
        }

        $request->validate([
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'two_factor_code'       => ['required', 'string'],
            'two_factor_secret'     => ['required', 'string'],
        ]);

        $secret = $request->input('two_factor_secret');

        if (! $totp->verify($secret, $request->input('two_factor_code'))) {
            return back()->withErrors([
                'two_factor_code' => 'Neplatný ověřovací kód. Zkontrolujte, zda jste naskenovali správný QR kód a zkuste to znovu.',
            ])->withInput();
        }

        $recoveryCodes = $totp->generateRecoveryCodesWithHashes();

        $admin->update([
            'password'                  => Hash::make($request->input('password')),
            'two_factor_secret'         => $secret,
            'two_factor_confirmed_at'   => now(),
        ]);
        $admin->setRecoveryCodes($recoveryCodes['hashed']);
        $admin->save();

        $request->session()->forget('admin.setup.two_factor_secret');
        $request->session()->regenerate();
        AuditLogger::rememberSessionStart($request, $admin);

        return redirect()->route('admin.setup.recovery')
            ->with('recovery_codes', $recoveryCodes['plain']);
    }

    public function showSetupRecovery(Request $request)
    {
        if (! $request->session()->has('recovery_codes')) {
            return redirect()->route('admin.dashboard');
        }

        $codes = $request->session()->get('recovery_codes');

        return view('admin.recovery-codes', compact('codes'));
    }

    public function enableTwoFactor(Request $request, TotpService $totp)
    {
        $admin = $this->guard()->user();

        if ($admin->hasTwoFactorEnabled()) {
            return redirect()->route('admin.dashboard')->with('error', 'Dvoufázové ověření je již aktivní.');
        }

        $request->validate([
            'two_factor_code'   => ['required', 'string'],
            'two_factor_secret' => ['required', 'string'],
        ]);

        $secret = $request->input('two_factor_secret');

        if (! $totp->verify($secret, $request->input('two_factor_code'))) {
            return back()->withErrors([
                'two_factor_code' => 'Neplatný ověřovací kód. Zkuste to znovu.',
            ]);
        }

        $recoveryCodes = $totp->generateRecoveryCodesWithHashes();

        $admin->update([
            'two_factor_secret'       => $secret,
            'two_factor_confirmed_at' => now(),
        ]);
        $admin->setRecoveryCodes($recoveryCodes['hashed']);
        $admin->save();

        return redirect()->route('admin.dashboard')
            ->with('success', 'Dvoufázové ověření bylo aktivováno.')
            ->with('recovery_codes', $recoveryCodes['plain'])
            ->with('show_recovery_modal', true);
    }

    public function disableTwoFactor(Request $request, TotpService $totp)
    {
        $admin = $this->guard()->user();

        if (! $admin->hasTwoFactorEnabled()) {
            return redirect()->route('admin.dashboard')->with('error', 'Dvoufázové ověření není aktivní.');
        }

        $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (! Hash::check($request->input('password'), $admin->password)) {
            return back()->withErrors([
                'password' => 'Zadané heslo není správné.',
            ]);
        }

        $admin->clearTwoFactor();
        $admin->save();

        return redirect()->route('admin.dashboard')->with('success', 'Dvoufázové ověření bylo deaktivováno.');
    }

    public function regenerateRecoveryCodes(Request $request, TotpService $totp)
    {
        $admin = $this->guard()->user();

        if (! $admin->hasTwoFactorEnabled()) {
            return redirect()->route('admin.dashboard')->with('error', 'Dvoufázové ověření není aktivní.');
        }

        $recoveryCodes = $totp->generateRecoveryCodesWithHashes();
        $admin->setRecoveryCodes($recoveryCodes['hashed']);
        $admin->save();

        return redirect()->route('admin.dashboard')
            ->with('success', 'Nové záložní kódy byly vygenerovány.')
            ->with('recovery_codes', $recoveryCodes['plain'])
            ->with('show_recovery_modal', true);
    }

    private function sendLoginTicket(Admin $admin): void
    {
        $token  = Str::random(32);
        $ticket = AdminLoginTicket::create([
            'admin_id'   => $admin->id,
            'token'      => $token,
            'expires_at' => now()->addMinutes(30),
        ]);

        $link = route('admin.login.verify', ['adminTicket' => $token]);

        try {
            Mail::to($admin->email)->send(new AdminLoginLinkMail($link, $ticket->expires_at));
        } catch (\Exception $e) {
            Log::error('Admin login email failed: ' . $e->getMessage());
        }

        Log::info("Admin login ticket for {$admin->email}: {$link}");
    }

    public function dashboard()
    {
        $stats = [
            'total'            => Application::count(),
            'submitted'        => Application::where('submitted', true)->count(),
            'drafts'           => Application::where('submitted', false)->count(),
            'awaiting_payment' => Application::where('submitted', true)->where('paid', false)->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    public function applications()
    {
        $applications = Application::with(['user', 'studyProgram', 'round'])
            ->orderBy('created_at', 'desc')
            ->get();

        $applicationsData = $applications
            ->load('attachments')
            ->map(function (Application $application) {
                $data = $application->toArray();
                unset($data['attachments']);
                $data['checkpoint_statuses'] = $application->checkpointStatuses();

                return $data;
            });

        $programs = StudyProgram::orderBy('name')->get();
        $rounds = ApplicationRound::with('studyProgram')->orderBy('academic_year')->orderBy('label')->get();

        return view('admin.applications.index', compact('applicationsData', 'programs', 'rounds'));
    }

    public function showApplication($id)
    {
        $application = Application::with(['user', 'studyProgram', 'attachments', 'round', 'applicationStatus'])
            ->findOrFail($id);

        $availableMoveRounds = $this->availableFurtherRoundsFor($application);

        AuditLogger::log(request(), AuditActionType::VIEW, $application, AuditLog::DESCRIPTION_VIEW_APPLICATION);

        return view('admin.applications.show', compact('application', 'availableMoveRounds'));
    }

    public function moveToFurtherRound(Request $request, $id, ApplicationStatusManager $statusManager)
    {
        $application = Application::with(['round', 'studyProgram', 'user', 'applicationStatus'])->findOrFail($id);

        if (! $application->completionDeadlinePassed() || $application->applicantCompletionRequirementsMet()) {
            return redirect()->route('admin.applications.show', $id)
                ->with('error', 'Přihlášku lze přesunout pouze tehdy, pokud nebyla dokončena včas.');
        }

        $availableMoveRounds = $this->availableFurtherRoundsFor($application);

        if ($availableMoveRounds->isEmpty()) {
            return redirect()->route('admin.applications.show', $id)
                ->with('error', 'Pro tuto přihlášku není k dispozici žádné jiné přijímací kolo.');
        }

        $validated = $request->validate([
            'target_round_id' => ['required', Rule::in($availableMoveRounds->pluck('id')->all())],
        ], [], [
            'target_round_id' => 'cílové kolo',
        ]);

        $targetRound = $availableMoveRounds->firstWhere('id', (int) $validated['target_round_id']);

        if (! $targetRound) {
            return redirect()->route('admin.applications.show', $id)
                ->with('error', 'Vybrané přijímací kolo není platné.');
        }

        if ($targetRound->isFull()) {
            return redirect()->route('admin.applications.show', $id)
                ->with('error', 'Vybrané přijímací kolo je už zaplněné.');
        }

        $statusManager->moveToFurtherRound($application, $targetRound);

        AuditLogger::log($request, AuditActionType::EDIT, $application->fresh(['round']), AuditLog::DESCRIPTION_MOVE_TO_FURTHER_ROUND);

        return redirect()->route('admin.applications.show', $id)
            ->with('success', 'Přihláška byla přesunuta do jiného kola.');
    }

    public function updateEvidenceNumber(Request $request, $id)
    {
        $application = Application::findOrFail($id);

        $validated = $request->validate([
            'evidence_number' => 'required|string|max:50|unique:applications,evidence_number,' . $application->id,
        ], [
            'evidence_number.required' => html_entity_decode('Eviden&#269;n&#237; &#269;&#237;slo je povinn&#233;.', ENT_QUOTES, 'UTF-8'),
            'evidence_number.unique' => html_entity_decode('Toto eviden&#269;n&#237; &#269;&#237;slo u&#382; existuje.', ENT_QUOTES, 'UTF-8'),
        ]);

        $application->update([
            'evidence_number' => $validated['evidence_number'],
        ]);

        AuditLogger::log($request, AuditActionType::EDIT, $application, AuditLog::DESCRIPTION_UPDATE_EVIDENCE_NUMBER);

        return redirect()->route('admin.applications.show', $id)
            ->with('success', 'Evidencni cislo bylo ulozeno.');
    }

    public function exportApplicationCsv($id): StreamedResponse
    {
        $application = $this->loadApplicationForExport($id);
        $filename = 'prihlaska-' . ($application->evidence_number ?: $application->application_number ?: $application->id) . '.csv';
        $columns = $this->csvColumns($application);

        AuditLogger::log(request(), AuditActionType::EXPORT, $application, AuditLog::DESCRIPTION_EXPORT_APPLICATION_CSV);

        return response()->streamDownload(function () use ($columns) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, array_keys($columns), ';');
            fputcsv($handle, array_values($columns), ';');
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportApplicationPdf($id): Response
    {
        $application = $this->loadApplicationForExport($id);
        AuditLogger::log(request(), AuditActionType::EXPORT, $application, AuditLog::DESCRIPTION_EXPORT_APPLICATION_PDF);
        $pdf = Pdf::loadView('admin.applications.pdf', [
            'application' => $application,
        ])->setPaper('a4');

        return $pdf->download('prihlaska-' . ($application->evidence_number ?: $application->application_number ?: $application->id) . '.pdf');
    }

    public function exportApplicationZip(Request $request, $id): Response
    {
        $application = $this->loadApplicationForExport($id);
        $baseName = $application->evidence_number ?: $application->application_number ?: $application->id;

        AuditLogger::log($request, AuditActionType::EXPORT, $application, AuditLog::DESCRIPTION_EXPORT_APPLICATION_CSV);

        $zipPath = storage_path('app/temp/' . uniqid('export_', true) . '.zip');
        $dir = dirname($zipPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE) !== true) {
            abort(500, 'Nelze vytvořit ZIP archiv.');
        }

        if ($request->boolean('csv')) {
            $columns = $this->csvColumns($application);
            $csv = '';
            $handle = fopen('php://temp', 'r+');
            fwrite($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, array_keys($columns), ';');
            fputcsv($handle, array_values($columns), ';');
            rewind($handle);
            $csv = stream_get_contents($handle);
            fclose($handle);
            $zip->addFromString("{$baseName}.csv", $csv);
        }

        if ($request->boolean('pdf')) {
            $pdf = Pdf::loadView('admin.applications.pdf', ['application' => $application])->setPaper('a4');
            $zip->addFromString("{$baseName}.pdf", $pdf->output());
        }

        if ($request->boolean('education')) {
            foreach ($application->attachments->whereIn('type', ['maturita', 'half_year_report']) as $attachment) {
                $diskPath = Storage::disk('public')->path($attachment->disk_path);
                if (file_exists($diskPath)) {
                    $zip->addFile($diskPath, 'doklady-vzdelani/' . $attachment->filename);
                }
            }
        }

        if ($request->boolean('payment')) {
            foreach ($application->attachments->where('type', 'payment') as $attachment) {
                $diskPath = Storage::disk('public')->path($attachment->disk_path);
                if (file_exists($diskPath)) {
                    $zip->addFile($diskPath, 'platba/' . $attachment->filename);
                }
            }
        }

        if ($request->boolean('other')) {
            foreach ($application->attachments->where('type', 'other') as $attachment) {
                $diskPath = Storage::disk('public')->path($attachment->disk_path);
                if (file_exists($diskPath)) {
                    $zip->addFile($diskPath, 'prilohy/' . $attachment->filename);
                }
            }
        }

        $zip->close();

        return response()->download($zipPath, "{$baseName}.zip")->deleteFileAfterSend();
    }

    public function bulkExportCsv(Request $request): Response
    {
        $ids = $this->validateBulkIds($request);
        $applications = Application::with(['user', 'studyProgram', 'round'])->whereIn('id', $ids)->get();

        return $this->bulkZip($applications, function ($app, $zip, $dir) {
            $columns = $this->csvColumns($app);
            $csv = '';
            $handle = fopen('php://temp', 'r+');
            fwrite($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, array_keys($columns), ';');
            fputcsv($handle, array_values($columns), ';');
            rewind($handle);
            $csv = stream_get_contents($handle);
            fclose($handle);
            $name = $app->evidence_number ?: $app->application_number ?: $app->id;
            $zip->addFromString("{$dir}/{$name}.csv", $csv);
        }, 'csv');
    }

    public function bulkExportPdf(Request $request): Response
    {
        $ids = $this->validateBulkIds($request);
        $applications = Application::with(['user', 'studyProgram', 'round'])->whereIn('id', $ids)->get();

        return $this->bulkZip($applications, function ($app, $zip, $dir) {
            $pdf = Pdf::loadView('admin.applications.pdf', ['application' => $app])->setPaper('a4');
            $name = $app->evidence_number ?: $app->application_number ?: $app->id;
            $zip->addFromString("{$dir}/{$name}.pdf", $pdf->output());
        }, 'pdf');
    }

    public function bulkExportZip(Request $request): Response
    {
        $ids = $this->validateBulkIds($request);
        $applications = Application::with(['user', 'studyProgram', 'round', 'attachments'])->whereIn('id', $ids)->get();

        $includeCsv = $request->boolean('csv');
        $includePdf = $request->boolean('pdf');
        $includeEducation = $request->boolean('education');
        $includePayment = $request->boolean('payment');
        $includeOther = $request->boolean('other');

        return $this->bulkZip($applications, function ($app, $zip, $dir) use ($includeCsv, $includePdf, $includeEducation, $includePayment, $includeOther) {
            $name = $app->evidence_number ?: $app->application_number ?: $app->id;

            if ($includeCsv) {
                $columns = $this->csvColumns($app);
                $csv = '';
                $handle = fopen('php://temp', 'r+');
                fwrite($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
                fputcsv($handle, array_keys($columns), ';');
                fputcsv($handle, array_values($columns), ';');
                rewind($handle);
                $csv = stream_get_contents($handle);
                fclose($handle);
                $zip->addFromString("{$dir}/{$name}.csv", $csv);
            }

            if ($includePdf) {
                $pdf = Pdf::loadView('admin.applications.pdf', ['application' => $app])->setPaper('a4');
                $zip->addFromString("{$dir}/{$name}.pdf", $pdf->output());
            }

            if ($includeEducation) {
                foreach ($app->attachments->whereIn('type', ['maturita', 'half_year_report']) as $att) {
                    $diskPath = Storage::disk('public')->path($att->disk_path);
                    if (file_exists($diskPath)) {
                        $zip->addFile($diskPath, "{$dir}/doklady-vzdelani/{$att->filename}");
                    }
                }
            }

            if ($includePayment) {
                foreach ($app->attachments->where('type', 'payment') as $att) {
                    $diskPath = Storage::disk('public')->path($att->disk_path);
                    if (file_exists($diskPath)) {
                        $zip->addFile($diskPath, "{$dir}/platba/{$att->filename}");
                    }
                }
            }

            if ($includeOther) {
                foreach ($app->attachments->where('type', 'other') as $att) {
                    $diskPath = Storage::disk('public')->path($att->disk_path);
                    if (file_exists($diskPath)) {
                        $zip->addFile($diskPath, "{$dir}/prilohy/{$att->filename}");
                    }
                }
            }
        }, 'export');
    }

    private function validateBulkIds(Request $request): array
    {
        $ids = $request->input('ids', []);
        if (! is_array($ids) || empty($ids)) {
            abort(422, 'Nebyly vybrány žádné přihlášky.');
        }
        return array_map('intval', $ids);
    }

    private function bulkZip($applications, callable $addFiles, string $suffix): Response
    {
        $zipPath = storage_path('app/temp/' . uniqid('bulk_', true) . '.zip');
        $dir = dirname($zipPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE) !== true) {
            abort(500, 'Nelze vytvořit ZIP archiv.');
        }

        foreach ($applications as $app) {
            $name = $app->evidence_number ?: $app->application_number ?: $app->id;
            $addFiles($app, $zip, $name);
        }

        $zip->close();

        $count = $applications->count();
        return response()->download($zipPath, "export-{$count}-prihlasek-{$suffix}.zip")->deleteFileAfterSend();
    }

    public function acceptPayment($id)
    {
        $application = Application::findOrFail($id);

        if (! $application->submitted) {
            return redirect()->route('admin.applications.show', $id)
                ->with('error', html_entity_decode('Platbu lze potvrdit a&#382; po odesl&#225;n&#237; p&#345;ihl&#225;&#353;ky.', ENT_QUOTES, 'UTF-8'));
        }

        $acceptedAt = now();

        $application->update([
            'payment_accepted' => true,
            'payment_accepted_at' => $acceptedAt,
            'payment_notified_at' => null,
        ]);

        $this->scheduleApplicantNotification($application, 'payment', $acceptedAt);
        AuditLogger::log(request(), AuditActionType::EDIT, $application, AuditLog::DESCRIPTION_ACCEPT_PAYMENT);

        return redirect()->route('admin.applications.show', $id)
            ->with('success', 'Platba byla potvrzena.');
    }

    public function revertPayment($id)
    {
        $application = Application::findOrFail($id);
        $application->update([
            'payment_accepted' => false,
            'payment_accepted_at' => null,
            'payment_notified_at' => null,
        ]);
        AuditLogger::log(request(), AuditActionType::EDIT, $application, AuditLog::DESCRIPTION_REVERT_PAYMENT);
        return redirect()->route('admin.applications.show', $id)
            ->with('success', 'Potvrzení platby bylo zrušeno.');
    }

    public function acceptEducation($id)
    {
        $application = Application::with('attachments')->findOrFail($id);

        if (! $application->submitted) {
            return redirect()->route('admin.applications.show', $id)
                ->with('error', html_entity_decode('Vzd&#283;l&#225;n&#237; lze uznat a&#382; po odesl&#225;n&#237; p&#345;ihl&#225;&#353;ky.', ENT_QUOTES, 'UTF-8'));
        }

        if ($application->bring_maturita_in_person && $application->attachments->where('type', 'maturita')->isEmpty()) {
            return redirect()->route('admin.applications.show', $id)
                ->with('error', html_entity_decode('P&#345;ed uzn&#225;n&#237;m vzd&#283;l&#225;n&#237; je pot&#345;eba nahr&#225;t not&#225;&#345;sky ov&#283;&#345;en&#233; maturitn&#237; vysv&#283;d&#269;en&#237;.', ENT_QUOTES, 'UTF-8'));
        }

        $acceptedAt = now();

        $application->update([
            'prev_study_info_accepted' => true,
            'education_accepted_at' => $acceptedAt,
            'education_notified_at' => null,
        ]);

        $this->scheduleApplicantNotification($application, 'education', $acceptedAt);
        AuditLogger::log(request(), AuditActionType::EDIT, $application, AuditLog::DESCRIPTION_ACCEPT_EDUCATION);

        return redirect()->route('admin.applications.show', $id)
            ->with('success', html_entity_decode('Vzd&#283;l&#225;n&#237; bylo uzn&#225;no.', ENT_QUOTES, 'UTF-8'));
    }
    public function revertEducation($id)
    {
        $application = Application::findOrFail($id);
        $application->update([
            'prev_study_info_accepted' => false,
            'education_accepted_at' => null,
            'education_notified_at' => null,
        ]);
        AuditLogger::log(request(), AuditActionType::EDIT, $application, AuditLog::DESCRIPTION_REVERT_EDUCATION);
        return redirect()->route('admin.applications.show', $id)
            ->with('success', 'Uznání vzdělání bylo zrušeno.');
    }

    public function uploadEducationAttachment(Request $request, $id)
    {
        $application = Application::findOrFail($id);

        if ($application->prev_study_info_accepted) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Po uznání vzdělání už nelze dokument měnit.'], 403)
                : redirect()->route('admin.applications.show', $id)->with('error', 'Po uznání vzdělání už nelze dokument měnit.');
        }

        $fieldName = $request->input('field_name');
        $type = $request->input('type');

        if (! $type && $fieldName === 'maturita_file') {
            $type = 'maturita';
        }

        if (! in_array($type, ['maturita'], true)) {
            return $request->expectsJson()
                ? response()->json(['message' => html_entity_decode('Nezn&#225;m&#253; typ souboru.', ENT_QUOTES, 'UTF-8')], 422)
                : back()->withErrors(['file' => html_entity_decode('Nezn&#225;m&#253; typ souboru.', ENT_QUOTES, 'UTF-8')]);
        }

        $validated = $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $old = $application->attachments()->where('type', $type)->first();
        if ($old) {
            Storage::disk('public')->delete($old->disk_path);
            $old->delete();
        }

        $file = $validated['file'];
        $path = $file->store('applications/' . $application->id, 'public');

        $attachment = $application->attachments()->create([
            'type' => $type,
            'filename' => $file->getClientOriginalName(),
            'disk_path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        $application->evaluateStates();
        AuditLogger::log($request, AuditActionType::EDIT, $application, AuditLog::DESCRIPTION_UPLOAD_ATTACHMENT);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'attachmentId' => $attachment->id,
                'filename' => $attachment->filename,
                'size' => $attachment->size,
                'mime_type' => $attachment->mime_type,
                'url' => asset('storage/' . $attachment->disk_path),
            ]);
        }

        return redirect()->route('admin.applications.show', $id)
            ->with('success', 'Dokument byl nahrán.');
    }

    public function deleteEducationAttachment(Request $request, $id, $attachmentId)
    {
        $attachment = ApplicationAttachment::where('application_id', $id)->findOrFail($attachmentId);
        $application = $attachment->application;

        if ($application->prev_study_info_accepted) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Po uznání vzdělání už nelze dokument odebrat.'], 403)
                : redirect()->route('admin.applications.show', $id)->with('error', 'Po uznání vzdělání už nelze dokument odebrat.');
        }

        if ($attachment->type !== 'maturita') {
            abort(404);
        }

        Storage::disk('public')->delete($attachment->disk_path);
        $attachment->delete();
        $application->evaluateStates();
        AuditLogger::log($request, AuditActionType::DELETE, $application, AuditLog::DESCRIPTION_DELETE_ATTACHMENT);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->route('admin.applications.show', $id)
            ->with('success', 'Dokument byl odstraněn.');
    }

    public function downloadAttachment(Request $request, $id, $attachmentId): Response
    {
        $attachment = ApplicationAttachment::where('application_id', $id)->findOrFail($attachmentId);
        $application = $attachment->application()->with(['user', 'studyProgram', 'attachments', 'round'])->firstOrFail();

        AuditLogger::log($request, AuditActionType::EXPORT, $application, AuditLog::DESCRIPTION_DOWNLOAD_ATTACHMENT);

        return response()->file(Storage::disk('public')->path($attachment->disk_path), [
            'Content-Type' => $attachment->mime_type,
            'Content-Disposition' => 'inline; filename="' . addslashes($attachment->filename) . '"',
        ]);
    }
    public function updateEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:admins,email,' . $this->guard()->id(),
        ]);
        $this->guard()->user()->update(['email' => $request->email]);
        return redirect()->route('admin.dashboard')->with('success', 'E-mail byl změněn.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate(['password' => 'required|min:8|confirmed']);
        $this->guard()->user()->update(['password' => Hash::make($request->password)]);
        return redirect()->route('admin.dashboard')->with('success', 'Heslo bylo uloženo.');
    }
    private function scheduleApplicantNotification(Application $application, string $type, $acceptedAt): void
    {
        $settings = WebsiteSetting::current();

        dispatch(new SendDelayedApplicationNotification(
            applicationId: $application->id,
            type: $type,
            expectedAcceptedAt: $acceptedAt->toIso8601String(),
        ))->delay(now()->addMinutes($settings->applicant_notification_delay_minutes));
    }

    private function availableFurtherRoundsFor(Application $application)
    {
        if (! $application->round) {
            return collect();
        }

        return ApplicationRound::query()
            ->where('study_program_id', $application->study_program_id)
            ->where('id', '!=', $application->round_id)
            ->where('completion_deadline_at', '>', $application->round->completion_deadline_at)
            ->orderBy('opens_at')
            ->get();
    }

    private function loadApplicationForExport($id): Application
    {
        return Application::with(['user', 'studyProgram', 'attachments', 'round'])
            ->findOrFail($id);
    }

    private function csvColumns(Application $application): array
    {
        $phone = $application->phone ?: '';

        return [
            'PRIJMENI' => $application->last_name ?: '',
            'JMENO' => $application->first_name ?: '',
            'TB_OKRES' => '',
            'DATUM_NAR' => $application->birth_date?->format('d.m.Y') ?: '',
            'RODNE_C' => $application->birth_number ?: '',
            'TELEFON' => $phone,
            'TEL_MOBIL' => $phone,
            'POHLAVI' => $application->gender ?: '',
            'MISTO_NAR' => $application->birth_city ?: '',
            'OKRES_NAR' => '',
            'ST_PRISL' => $application->citizenship ?: '',
            'POZNAMKA' => $application->note ?: '',
            'KOD_ZP' => $application->studyProgram?->code ?: '',
            'PRIHL_OD' => $application->round?->academic_year ?: '',
            'EV_CISLO' => $application->evidence_number ?: $application->application_number ?: (string) $application->id,
            'E_MAIL' => $application->email ?: '',
        ];
    }
}
