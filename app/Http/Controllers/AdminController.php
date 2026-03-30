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
            $request->session()->regenerate();
            AuditLogger::rememberSessionStart($request, $this->guard()->user());
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

        $this->guard()->login($ticket->admin);
        $ticket->update(['used_at' => now()]);
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

        return view('admin.applications.index', compact('applicationsData', 'programs'));
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
                ->with('error', 'Pro tuto přihlášku není k dispozici žádné další přijímací kolo.');
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
            ->with('success', 'Přihláška byla přesunuta do dalšího kola.');
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
            ->where('academic_year', $application->round->academic_year)
            ->where('is_active', true)
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
