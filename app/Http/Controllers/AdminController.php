<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Mail\AdminLoginLinkMail;
use App\Models\Admin;
use App\Models\AdminLoginTicket;
use App\Models\Application;
use App\Models\StudyProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
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

        $programs = StudyProgram::orderBy('name')->get();

        return view('admin.applications.index', compact('applications', 'programs'));
    }

    public function showApplication($id)
    {
        $application = Application::with(['user', 'studyProgram', 'attachments', 'round'])
            ->findOrFail($id);

        return view('admin.applications.show', compact('application'));
    }

    public function updateEvidenceNumber(Request $request, $id)
    {
        $application = Application::findOrFail($id);

        $validated = $request->validate([
            'evidence_number' => 'required|string|max:50|unique:applications,evidence_number,' . $application->id,
        ]);

        $application->update([
            'evidence_number' => $validated['evidence_number'],
        ]);

        return redirect()->route('admin.applications.show', $id)
            ->with('success', 'Evidencni cislo bylo ulozeno.');
    }

    public function exportApplicationCsv($id): StreamedResponse
    {
        $application = $this->loadApplicationForExport($id);
        $filename = 'prihlaska-' . ($application->evidence_number ?: $application->application_number ?: $application->id) . '.csv';
        $columns = $this->csvColumns($application);

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
        $pdf = Pdf::loadView('admin.applications.pdf', [
            'application' => $application,
        ])->setPaper('a4');

        return $pdf->download('prihlaska-' . ($application->evidence_number ?: $application->application_number ?: $application->id) . '.pdf');
    }

    public function acceptPayment($id)
    {
        Application::findOrFail($id)->update(['payment_accepted' => true]);
        return redirect()->route('admin.applications.show', $id)
            ->with('success', 'Platba byla potvrzena.');
    }

    public function revertPayment($id)
    {
        Application::findOrFail($id)->update(['payment_accepted' => false]);
        return redirect()->route('admin.applications.show', $id)
            ->with('success', 'Potvrzení platby bylo zrušeno.');
    }

    public function acceptEducation($id)
    {
        Application::findOrFail($id)->update(['prev_study_info_accepted' => true]);
        return redirect()->route('admin.applications.show', $id)
            ->with('success', 'Vzdělání bylo uznáno.');
    }

    public function revertEducation($id)
    {
        Application::findOrFail($id)->update(['prev_study_info_accepted' => false]);
        return redirect()->route('admin.applications.show', $id)
            ->with('success', 'Uznání vzdělání bylo zrušeno.');
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
