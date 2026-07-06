<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\ApplicationRound;
use App\Models\DashboardPreset;
use App\Models\StudyProgram;
use App\Models\AuditActionType;
use App\Models\AuditLog;
use App\Models\WebsiteSetting;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MainAdminController extends Controller
{
    public function rounds()
    {
        $programs = StudyProgram::with([
            'applicationRounds' => fn($query) => $query->orderByDesc('opens_at'),
        ])
            ->withCount(['applications', 'applicationRounds' => fn($q) => $q->where('academic_year', date('Y') . '/' . (date('Y') + 1))])
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        return view('admin.rounds', compact('programs'));
    }

    public function settings()
    {
        $settings = WebsiteSetting::current();

        return view('admin.settings', compact('settings'));
    }

    public function auditLogs(Request $request)
    {
        AuditLogger::log($request, AuditActionType::VIEW, null, AuditLog::DESCRIPTION_VIEW_AUDIT_LOG);

        $query = AuditLog::query()->with(['admin', 'actionType', 'application']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('ip_address', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('admin', fn($a) => $a->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                    ->orWhereHas('application', fn($a) => $a->where('evidence_number', 'like', "%{$search}%")->orWhere('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%"));
            });
        }

        $logs = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('admin.audit-logs', compact('logs'));
    }

    public function exportAuditLogs(Request $request): \Illuminate\Http\Response
    {
        AuditLogger::log($request, AuditActionType::EXPORT, null, AuditLog::DESCRIPTION_EXPORT_AUDIT_LOG);

        $query = AuditLog::query()->with(['admin', 'actionType', 'application']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('ip_address', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('admin', fn($a) => $a->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                    ->orWhereHas('application', fn($a) => $a->where('evidence_number', 'like', "%{$search}%")->orWhere('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%"));
            });
        }

        $logs = $query->orderByDesc('created_at')->get();

        $descriptionLabels = [
            AuditLog::DESCRIPTION_VIEW_APPLICATION => 'Zobrazení detailu přihlášky',
            AuditLog::DESCRIPTION_VIEW_AUDIT_LOG => 'Zobrazení auditního logu',
            AuditLog::DESCRIPTION_UPDATE_EVIDENCE_NUMBER => 'Úprava evidenčního čísla',
            AuditLog::DESCRIPTION_EXPORT_APPLICATION_CSV => 'Export přihlášky do CSV',
            AuditLog::DESCRIPTION_EXPORT_APPLICATION_PDF => 'Export přihlášky do PDF',
            AuditLog::DESCRIPTION_EXPORT_APPLICATION_ZIP => 'Export přihlášky do ZIP',
            AuditLog::DESCRIPTION_BULK_EXPORT_CSV => 'Hromadný export CSV',
            AuditLog::DESCRIPTION_BULK_EXPORT_PDF => 'Hromadný export PDF',
            AuditLog::DESCRIPTION_BULK_EXPORT_ZIP => 'Hromadný export ZIP',
            AuditLog::DESCRIPTION_EXPORT_AUDIT_LOG => 'Export auditního logu',
            AuditLog::DESCRIPTION_DOWNLOAD_ATTACHMENT => 'Stažení přílohy',
            AuditLog::DESCRIPTION_UPLOAD_ATTACHMENT => 'Nahrání přílohy',
            AuditLog::DESCRIPTION_DELETE_ATTACHMENT => 'Smazání přílohy',
            AuditLog::DESCRIPTION_ACCEPT_EDUCATION => 'Uznání vzdělání',
            AuditLog::DESCRIPTION_REVERT_EDUCATION => 'Zrušení uznání vzdělání',
            AuditLog::DESCRIPTION_ACCEPT_PAYMENT => 'Potvrzení platby',
            AuditLog::DESCRIPTION_REVERT_PAYMENT => 'Zrušení potvrzení platby',
            AuditLog::DESCRIPTION_MOVE_TO_FURTHER_ROUND => 'Přesun do dalšího kola',
        ];

        $csv = '';
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($handle, ['Datum a čas', 'Administrátor', 'E-mail', 'Akce', 'Událost', 'Přihláška', 'Uchazeč', 'IP adresa'], ';');

        foreach ($logs as $log) {
            fputcsv($handle, [
                $log->created_at?->format('j. n. Y H:i:s') ?? '—',
                $log->admin?->name ?? '—',
                $log->admin?->email ?? '—',
                $log->actionType?->label ?? '—',
                $descriptionLabels[$log->description] ?? ($log->description ?: '—'),
                $log->application ? ($log->application->evidence_number ?: $log->application->application_number ?: '#' . $log->application->id) : '—',
                $log->application ? trim(($log->application->first_name ?? '') . ' ' . ($log->application->last_name ?? '')) : '—',
                $log->ip_address ?? '—',
            ], ';');
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="audit-log-' . now()->format('Y-m-d-Hi') . '.csv"',
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $settings = WebsiteSetting::current();

        $validated = $request->validate([
            'application_fee' => ['required', 'integer', 'min:0', 'max:999999'],
            'notification_email' => ['required', 'email', 'max:255'],
            'bank_account' => ['required', 'string', 'max:34', 'regex:/^CZ\d{22}$/i'],
            'applicant_notification_delay_minutes' => ['required', 'integer', 'min:0', 'max:10080'],
        ], [], [
            'application_fee' => 'cena přihlášky',
            'notification_email' => 'notifikační e-mail',
            'bank_account' => 'číslo účtu',
            'applicant_notification_delay_minutes' => 'prodleva notifikace uchazeči',
        ]);

        $settings->update($validated);

        return redirect()->route('admin.settings')->with('success', 'Nastavení webu bylo uloženo.');
    }

    public function storeProgram(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->programRules(), [], $this->programAttributes());

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, 'programCreate')
                ->withInput()
                ->with('open_modal', 'create-program');
        }

        StudyProgram::create($this->normalizeProgramData($validator->validated()));

        return redirect()->route('admin.rounds')->with('success', 'Studijní program byl vytvořen.');
    }

    public function updateProgram(Request $request, StudyProgram $studyProgram): RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->programRules(), [], $this->programAttributes());

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, 'programUpdate')
                ->withInput()
                ->with('open_modal', 'edit-program-' . $studyProgram->id);
        }

        $studyProgram->update($this->normalizeProgramData($validator->validated()));

        return redirect()->route('admin.rounds')->with('success', 'Studijní program byl upraven.');
    }

    public function destroyProgram(StudyProgram $studyProgram): RedirectResponse
    {
        $studyProgram->delete();

        return redirect()->route('admin.rounds')->with('success', 'Studijní program byl odstraněn.');
    }

    public function storeRound(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->roundRules(), [], $this->roundAttributes());

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, 'roundCreate')
                ->withInput()
                ->with('open_modal', $request->input('open_modal_target', 'create-round'));
        }

        ApplicationRound::create($this->normalizeRoundData($validator->validated()));

        return redirect()->route('admin.rounds')->with('success', 'Přijímací kolo bylo vytvořeno.');
    }

    public function updateRound(Request $request, ApplicationRound $applicationRound): RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->roundRules(), [], $this->roundAttributes());

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, 'roundUpdate')
                ->withInput()
                ->with('open_modal', 'edit-round-' . $applicationRound->id);
        }

        $applicationRound->update($this->normalizeRoundData($validator->validated()));

        return redirect()->route('admin.rounds')->with('success', 'Přijímací kolo bylo upraveno.');
    }

    public function destroyRound(ApplicationRound $applicationRound): RedirectResponse
    {
        $applicationRound->delete();

        return redirect()->route('admin.rounds')->with('success', 'Přijímací kolo bylo odstraněno.');
    }

    public function admins()
    {
        $admins = Admin::query()
            ->orderByDesc('is_main_admin')
            ->orderBy('name')
            ->orderBy('email')
            ->get();

        return view('admin.admins', compact('admins'));
    }

    public function storeAdmin(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:admins,email'],
            'is_main_admin' => ['nullable', 'boolean'],
        ], [], [
            'name' => 'jméno',
            'email' => 'e-mail',
            'is_main_admin' => 'role',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, 'adminCreate')
                ->withInput()
                ->with('open_modal', 'create-admin');
        }

        $data = $validator->validated();
        $data['is_main_admin'] = (bool) ($data['is_main_admin'] ?? false);

        Admin::create($data);

        return redirect()->route('admin.admins')->with('success', 'Administrátorský účet byl vytvořen.');
    }

    public function updateAdmin(Request $request, Admin $admin): RedirectResponse
    {
        $this->ensureNotSelf($admin);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('admins', 'email')->ignore($admin->id),
            ],
            'is_main_admin' => ['nullable', 'boolean'],
        ], [], [
            'name' => 'jméno',
            'email' => 'e-mail',
            'is_main_admin' => 'role',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, 'adminUpdate')
                ->withInput()
                ->with('open_modal', 'edit-admin-' . $admin->id);
        }

        $data = $validator->validated();
        $data['is_main_admin'] = (bool) ($data['is_main_admin'] ?? false);

        $admin->update($data);

        return redirect()->route('admin.admins')->with('success', 'Účet administrátora byl upraven.');
    }

    public function destroyAdmin(Admin $admin): RedirectResponse
    {
        $this->ensureNotSelf($admin);

        $admin->delete();

        return redirect()->route('admin.admins')->with('success', 'Administrátorský účet byl odstraněn.');
    }

    public function resetAdminTwoFactor(Admin $admin): RedirectResponse
    {
        $this->ensureNotSelf($admin);

        $admin->clearTwoFactor();
        $admin->update(['password' => null]);
        $admin->save();

        return redirect()->route('admin.admins')->with('success', 'Dvoufázové ověření administrátora ' . e($admin->name) . ' bylo resetováno. Administrátor si při příštím přihlášení nastaví nové heslo a 2FA.');
    }

    public function storePreset(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->presetRules(), [], $this->presetAttributes());

        $validated['sort_order'] = DashboardPreset::max('sort_order') + 1;
        DashboardPreset::create($validated);

        return redirect()->route('admin.dashboard')->with('success', 'Panel byl vytvořen.');
    }

    public function updatePreset(Request $request, DashboardPreset $dashboardPreset): RedirectResponse
    {
        $validated = $request->validate($this->presetRules(), [], $this->presetAttributes());

        $dashboardPreset->update($validated);

        return redirect()->route('admin.dashboard')->with('success', 'Panel byl upraven.');
    }

    public function destroyPreset(DashboardPreset $dashboardPreset): RedirectResponse
    {
        $dashboardPreset->delete();

        return redirect()->route('admin.dashboard')->with('success', 'Panel byl odstraněn.');
    }

    public function reorderPresets(Request $request): RedirectResponse
    {
        $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['required', 'integer', 'exists:dashboard_presets,id'],
        ]);

        foreach ($request->input('order') as $i => $id) {
            DashboardPreset::where('id', $id)->update(['sort_order' => $i]);
        }

        return redirect()->route('admin.dashboard')->with('success', 'Pořadí panelů bylo změněno.');
    }

    public function movePresetUp(DashboardPreset $dashboardPreset): RedirectResponse
    {
        $current = $dashboardPreset->sort_order;
        $above = DashboardPreset::where('sort_order', '<', $current)
            ->orderByDesc('sort_order')
            ->first();

        if ($above) {
            $dashboardPreset->update(['sort_order' => $above->sort_order]);
            $above->update(['sort_order' => $current]);
        }

        return redirect()->route('admin.dashboard')->with('success', 'Pořadí panelů bylo změněno.');
    }

    public function movePresetDown(DashboardPreset $dashboardPreset): RedirectResponse
    {
        $current = $dashboardPreset->sort_order;
        $below = DashboardPreset::where('sort_order', '>', $current)
            ->orderBy('sort_order')
            ->first();

        if ($below) {
            $dashboardPreset->update(['sort_order' => $below->sort_order]);
            $below->update(['sort_order' => $current]);
        }

        return redirect()->route('admin.dashboard')->with('success', 'Pořadí panelů bylo změněno.');
    }

    private function presetRules(): array
    {
        return [
            'label' => ['required', 'string', 'max:255'],
            'icon' => ['required', 'string', 'max:255'],
            'color_class' => ['required', 'string', 'max:255'],
            'checkpoint' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255', 'required_with:checkpoint'],
            'study_program_id' => ['nullable', 'integer', 'exists:study_programs,id'],
            'round_id' => ['nullable', 'integer', 'exists:application_rounds,id'],
        ];
    }

    private function presetAttributes(): array
    {
        return [
            'label' => 'popisek',
            'icon' => 'ikona',
            'color_class' => 'barva',
            'checkpoint' => 'krok',
            'state' => 'stav',
            'study_program_id' => 'studijní program',
            'round_id' => 'přijímací kolo',
        ];
    }

    private function ensureNotSelf(Admin $admin): void
    {
        if ($admin->is(Auth::guard('admin')->user())) {
            throw new HttpException(403, 'Vlastní administrátorský účet zde nelze spravovat.');
        }
    }

    private function programRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255'],
            'degree' => ['required', 'string', 'max:255'],
            'form' => ['required', 'string', 'max:255'],
            'length' => ['required', 'string', 'max:255'],
            'language' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'tuition_fee' => ['nullable', 'string', 'max:255'],
            'variable_symbol' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'image_path' => ['nullable', 'string', 'max:2048'],
            'info_url' => ['required', 'url', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    private function programAttributes(): array
    {
        return [
            'name' => 'název programu',
            'code' => 'kód programu',
            'degree' => 'titul',
            'form' => 'forma studia',
            'length' => 'délka studia',
            'language' => 'jazyk',
            'location' => 'místo studia',
            'tuition_fee' => 'školné',
            'variable_symbol' => 'variabilní symbol',
            'description' => 'popis programu',
            'image_path' => 'obrázek programu',
            'info_url' => 'odkaz na více informací',
        ];
    }

    private function normalizeProgramData(array $data): array
    {
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        foreach (['code', 'tuition_fee', 'variable_symbol', 'description', 'image_path'] as $field) {
            if (array_key_exists($field, $data) && $data[$field] === '') {
                $data[$field] = null;
            }
        }

        return $data;
    }

    private function roundRules(): array
    {
        return [
            'study_program_id' => ['required', 'exists:study_programs,id'],
            'academic_year' => ['required', 'string', 'max:50'],
            'label' => ['nullable', 'string', 'max:255'],
            'opens_at' => ['required', 'date'],
            'closes_at' => ['required', 'date', 'after:opens_at'],
            'completion_deadline_at' => ['required', 'date', 'after_or_equal:closes_at'],
            'max_applicants' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    private function roundAttributes(): array
    {
        return [
            'study_program_id' => 'studijní program',
            'academic_year' => 'akademický rok',
            'label' => 'označení kola',
            'opens_at' => 'datum otevření',
            'closes_at' => 'datum uzavření',
            'max_applicants' => 'maximální počet uchazečů',
        ];
    }

    private function normalizeRoundData(array $data): array
    {
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        foreach (['label', 'max_applicants'] as $field) {
            if (array_key_exists($field, $data) && $data[$field] === '') {
                $data[$field] = null;
            }
        }

        return $data;
    }
}
