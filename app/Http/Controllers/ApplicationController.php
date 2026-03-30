<?php

namespace App\Http\Controllers;

use App\Mail\StyledNotificationMail;
use App\Models\Application;
use App\Models\ApplicationAttachment;
use App\Models\ApplicationRound;
use App\Models\ApplicationStatus;
use App\Models\StudyProgram;
use App\Models\WebsiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ApplicationController extends Controller
{
    private const AUTOSAVE_RULES = [
        'first_name' => ['nullable', 'string', 'max:255'],
        'last_name' => ['nullable', 'string', 'max:255'],
        'gender' => ['nullable', 'string', 'in:Muž,Žena'],
        'birth_number' => ['nullable', 'string', 'max:20', 'regex:/^\d{6}\/?(\d{3,4})$/'],
        'birth_date' => ['nullable', 'date', 'before:today', 'after:1900-01-01'],
        'birth_city' => ['nullable', 'string', 'max:255'],
        'citizenship' => ['nullable', 'string', 'max:255'],
        'email' => ['nullable', 'email:rfc,dns', 'max:255'],
        'phone' => ['nullable', 'string', 'regex:/^(\+420)?\s?[1-9][0-9]{2}\s?[0-9]{3}\s?[0-9]{3}$/'],
        'street' => ['nullable', 'string', 'max:255'],
        'city' => ['nullable', 'string', 'max:255'],
        'zip' => ['nullable', 'string', 'regex:/^\d{3}\s?\d{2}$/'],
        'country' => ['nullable', 'string', 'max:255'],
        'previous_school' => ['nullable', 'string', 'max:255'],
        'izo' => ['nullable', 'string', 'max:50'],
        'school_type' => ['nullable', 'string', 'max:50'],
        'previous_study_field' => ['nullable', 'string', 'max:255'],
        'previous_study_field_code' => ['nullable', 'string', 'max:50'],
        'graduation_year' => ['nullable', 'digits:4', 'integer', 'min:1950', 'max:2030'],
        'grade_average' => ['nullable', 'numeric', 'min:1.00', 'max:5.00'],
        'half_year_grade_average' => ['nullable', 'numeric', 'min:1.00', 'max:5.00'],
        'maturita_grade_average' => ['nullable', 'numeric', 'min:1.00', 'max:5.00'],
        'bring_maturita_in_person' => ['nullable', 'boolean'],
        'specific_needs' => ['nullable', 'string'],
        'note' => ['nullable', 'string'],
        'gdpr_accepted' => ['nullable', 'boolean'],
    ];

    private const AUTOSAVE_MESSAGES = [
        'value.regex' => 'Hodnota není ve správném formátu.',
        'value.email' => 'E-mail není platná adresa.',
        'value.digits' => 'Musí být čtyřciferné číslo.',
        'value.min' => 'Hodnota je příliš malá.',
        'value.max' => 'Hodnota je příliš velká.',
        'value.numeric' => 'Musí být číslo.',
        'value.integer' => 'Musí být celé číslo.',
        'value.date' => 'Neplatné datum.',
        'value.before' => 'Datum musí být v minulosti.',
        'value.after' => 'Datum je příliš staré.',
        'value.in' => 'Neplatná hodnota.',
    ];

    private const NIA_LOCKED_FIELDS = [
        'first_name',
        'last_name',
        'birth_date',
        'street',
        'city',
        'zip',
    ];

    private const STEP1_FIELDS = [
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

    private const STEP2_FIELDS = [
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
    ];

    public function status(Request $request, $id)
    {
        $application = Application::where('user_id', Auth::id())
            ->with(['attachments', 'applicationStatus', 'round'])
            ->findOrFail($id);

        return response()->json($application->statusPanelData());
    }

    public function programsIndex()
    {
        $programs = StudyProgram::where('is_active', true)
            ->with(['applicationRounds' => fn($q) => $q->where('is_active', true)->orderBy('opens_at')])
            ->get()
            ->filter(fn($p) => $p->hasAnyRounds())
            ->values();

        return view('programs.index', compact('programs'));
    }

    public function create(Request $request, $program_id)
    {
        $program = StudyProgram::findOrFail($program_id);

        $round = ApplicationRound::where('study_program_id', $program_id)
            ->open()
            ->first();

        if (! $round) {
            return redirect()->route('programs.index')
                ->with('error', 'Pro tento studijní program momentálně neprobíhá žádné přijímací kolo.');
        }

        if ($round->isFull()) {
            return redirect()->route('programs.index')
                ->with('error', 'Kapacita tohoto kola je již naplněna.');
        }

        $existingDraft = Application::where('user_id', Auth::id())
            ->where('study_program_id', $program_id)
            ->where('submitted', false)
            ->first();

        if ($existingDraft) {
            return redirect()->route('application.step1', $existingDraft->id);
        }

        $alreadyAppliedThisYear = Application::where('user_id', Auth::id())
            ->where('study_program_id', $program_id)
            ->where('submitted', true)
            ->whereHas('round', fn($q) => $q->where('academic_year', $round->academic_year))
            ->exists();

        if ($alreadyAppliedThisYear) {
            return redirect()->route('programs.index')
                ->with('error', "Do tohoto programu jste v akademickém roce {$round->academic_year} již přihlášku podali.");
        }

        $app = Application::create([
            'user_id' => Auth::id(),
            'study_program_id' => $program->id,
            'round_id' => $round->id,
            'application_status_id' => ApplicationStatus::idFor(ApplicationStatus::DRAFT),
            'status_changed_at' => now(),
            'email' => Auth::user()->email,
        ]);

        $app->update([
            'evidence_number' => $this->makeEvidenceNumber($app),
        ]);

        return redirect()->route('application.step1', $app->id);
    }

    public function autosave(Request $request, $id)
    {
        $application = Application::where('user_id', Auth::id())->findOrFail($id);

        $field = $request->input('field');

        if (! array_key_exists($field, self::AUTOSAVE_RULES)) {
            return response()->json(['error' => 'Neznámé pole.'], 422);
        }

        if ($application->completionDeadlinePassed()) {
            return response()->json(['error' => 'Po uplynutí termínu už nelze přihlášku upravovat.'], 403);
        }

        if ($application->submitted && in_array($field, self::STEP1_FIELDS, true)) {
            return response()->json(['error' => 'Tato sekce je uzamčena.'], 403);
        }

        if ($application->submitted && $field === 'gdpr_accepted') {
            return response()->json(['error' => 'Souhlas s GDPR je již uzamčen.'], 403);
        }

        if ($application->prev_study_info_accepted && in_array($field, self::STEP2_FIELDS, true)) {
            return response()->json(['error' => 'Sekce vzdělání už byla uznána školou a nelze ji měnit.'], 403);
        }

        $verifiedFields = $application->verified_fields ?? [];
        if (in_array($field, self::NIA_LOCKED_FIELDS, true) && in_array($field, $verifiedFields, true)) {
            return response()->json(['error' => 'Toto pole bylo ověřeno pomocí Identity občana a nelze jej změnit.'], 403);
        }

        $validator = Validator::make(
            ['value' => $request->input('value')],
            ['value' => self::AUTOSAVE_RULES[$field]],
            self::AUTOSAVE_MESSAGES
        );

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first('value')], 422);
        }

        $value = $validator->validated()['value'];
        if (in_array($field, ['gdpr_accepted', 'bring_maturita_in_person'], true)) {
            $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        if (in_array($field, ['birth_number', 'birth_date'], true)) {
            $birthNumber = $field === 'birth_number' ? $value : $application->birth_number;
            $birthDate = $field === 'birth_date' ? $value : $application->birth_date;

            if (! $this->birthNumberMatchesBirthDate($birthNumber, $birthDate)) {
                return response()->json([
                    'error' => 'Rodne cislo neodpovida zadanemu datu narozeni.',
                ], 422);
            }
        }

        $application->update([$field => $value]);
        $application->evaluateStates();

        return response()->json(['ok' => true]);
    }

    public function uploadAttachment(Request $request, $id)
    {
        $application = Application::where('user_id', Auth::id())->findOrFail($id);

        if ($application->completionDeadlinePassed()) {
            return response()->json(['message' => 'Po uplynutí termínu už nelze přikládat další soubory.'], 403);
        }

        $fieldName = $request->input('field_name');
        $type = match ($fieldName) {
            'maturita_file' => 'maturita',
            'half_year_report_file' => 'half_year_report',
            'other_files[]' => 'other',
            'payment_file' => 'payment',
            default => null,
        };

        if (! $type) {
            return response()->json(['message' => 'Neznámý typ souboru.'], 422);
        }

        if (in_array($type, ['maturita', 'half_year_report'], true) && $application->prev_study_info_accepted) {
            return response()->json(['message' => 'Sekce vzdělání už byla uznána školou a nelze ji měnit.'], 403);
        }

        if ($type === 'payment' && $application->payment_accepted) {
            return response()->json(['message' => 'Platba už byla potvrzena školou a nelze ji měnit.'], 403);
        }

        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        if (in_array($type, ['maturita', 'half_year_report', 'payment'], true)) {
            $old = $application->attachments()->where('type', $type)->first();
            if ($old) {
                Storage::disk('public')->delete($old->disk_path);
                $old->delete();
            }
        }

        $file = $request->file('file');
        $path = $file->store('applications/' . $application->id, 'public');
        $attachment = $application->attachments()->create([
            'type' => $type,
            'filename' => $file->getClientOriginalName(),
            'disk_path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        $application->evaluateStates();

        return response()->json([
            'ok' => true,
            'attachmentId' => $attachment->id,
            'filename' => $attachment->filename,
            'size' => $attachment->size,
            'mime_type' => $attachment->mime_type,
            'url' => asset('storage/' . $attachment->disk_path),
        ]);
    }

    public function deleteAttachment(Request $request, $id, $attachmentId)
    {
        $attachment = ApplicationAttachment::where('application_id', $id)->findOrFail($attachmentId);
        $app = $attachment->application;

        if ($app->user_id !== Auth::id()) {
            abort(403);
        }

        if ($app->completionDeadlinePassed()) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Po uplynutí termínu už nelze odebírat soubory.'], 403)
                : redirect()->back()->with('error', 'Po uplynutí termínu už nelze odebírat soubory.');
        }

        if (in_array($attachment->type, ['maturita', 'half_year_report'], true) && $app->prev_study_info_accepted) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Sekce vzdělání už byla uznána školou a nelze ji měnit.'], 403)
                : redirect()->back()->with('error', 'Sekce vzdělání už byla uznána školou a nelze ji měnit.');
        }

        if ($attachment->type === 'payment' && $app->payment_accepted) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Platba už byla potvrzena školou a nelze ji měnit.'], 403)
                : redirect()->back()->with('error', 'Platba už byla potvrzena školou a nelze ji měnit.');
        }

        Storage::disk('public')->delete($attachment->disk_path);
        $attachment->delete();
        $app->evaluateStates();

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->back()->with('success', 'Soubor byl odstraněn.');
    }

    public function step1($id)
    {
        $application = Application::where('user_id', Auth::id())->findOrFail($id);
        return view('application.personal', ['application' => $application, 'currentStep' => 1]);
    }

    public function step2($id)
    {
        $application = Application::where('user_id', Auth::id())->findOrFail($id);
        return view('application.education', ['application' => $application, 'currentStep' => 2]);
    }

    public function step3($id)
    {
        $application = Application::where('user_id', Auth::id())->findOrFail($id);
        return view('application.additional', ['application' => $application, 'currentStep' => 3]);
    }

    public function step4($id)
    {
        $application = Application::where('user_id', Auth::id())->findOrFail($id);
        $settings = WebsiteSetting::current();

        return view('application.payment', [
            'application' => $application,
            'currentStep' => 4,
            'settings' => $settings,
        ]);
    }

    public function step5($id)
    {
        $application = Application::where('user_id', Auth::id())->findOrFail($id);
        return view('application.summary', ['application' => $application, 'currentStep' => 5]);
    }

    public function submit(Request $request, $id)
    {
        $application = Application::where('user_id', Auth::id())->findOrFail($id);

        if ($application->submitted) {
            return redirect()->route('application.step5', $id);
        }

        if ($application->submissionDeadlinePassed()) {
            return redirect()->route('application.step5', $id)
                ->with('error', 'Termín pro odeslání přihlášky už uplynul.');
        }

        $application->gdpr_accepted = $request->boolean('consent');
        $application->save();

        $request->validate([
            'consent' => 'accepted',
        ], [
            'consent.accepted' => 'Musíte potvrdit souhlas se zpracováním údajů.',
        ]);

        if (! $application->identity_verified) {
            return redirect()->route('application.step1', $id)
                ->with('error', 'Před odesláním přihlášky musíte ověřit svou identitu přes Idenitu občana.');
        }

        if (! $application->isStep1Complete()) {
            return redirect()->route('application.step1', $id)
                ->with('error', 'Před odesláním musíte vyplnit všechny povinné osobní údaje.');
        }

        $fieldErrors = [];
        $strictChecks = [
            'email' => ['email:rfc', 'max:255'],
            'phone' => ['regex:/^(\+420)?\s?[1-9][0-9]{2}\s?[0-9]{3}\s?[0-9]{3}$/'],
            'zip' => ['regex:/^\d{3}\s?\d{2}$/'],
            'birth_number' => ['regex:/^\d{6}\/?(\d{3,4})$/'],
        ];

        foreach ($strictChecks as $field => $rules) {
            $v = Validator::make(
                [$field => $application->{$field}],
                [$field => array_merge(['nullable'], $rules)]
            );
            if ($v->fails()) {
                $fieldErrors[] = $field;
            }
        }

        if (! empty($fieldErrors)) {
            return redirect()->route('application.step1', $id)
                ->with('error', 'Některé povinné údaje jsou ve špatném formátu. Zkontrolujte prosím zvýrazněná pole.');
        }

        if (! $this->birthNumberMatchesBirthDate($application->birth_number, $application->birth_date)) {
            return redirect()->route('application.step1', $id)
                ->with('error', 'Rodne cislo musi odpovidat zadanemu datu narozeni.');
        }

        $submittedAt = now();

        $application->update([
            'submitted' => true,
            'submitted_at' => $submittedAt,
            'application_number' => date('Y') . str_pad($application->id, 4, '0', STR_PAD_LEFT),
            'application_status_id' => ApplicationStatus::idFor(ApplicationStatus::SUBMITTED),
            'status_changed_at' => $submittedAt,
            'status_notified_at' => null,
        ]);

        $application->evaluateStates();

        $this->notifySchoolAboutSubmittedApplication($application->fresh(['user', 'studyProgram', 'round']));

        return redirect()->route('application.step5', $id)
            ->with('success', 'Přihláška byla úspěšně odeslána!');
    }

    private function notifySchoolAboutSubmittedApplication(Application $application): void
    {
        $settings = WebsiteSetting::current();

        try {
            Mail::to($settings->notification_email)->send(new StyledNotificationMail(
                subjectLine: 'Byla odeslána nová přihláška',
                headline: 'Nová přihláška byla odeslána',
                lines: [
                    'Uchazeč ' . trim(($application->first_name ?? '') . ' ' . ($application->last_name ?? '')) . ' právě odeslal přihlášku.',
                    'Studijní program: ' . ($application->studyProgram?->name ?? '—') . '. Akademický rok: ' . ($application->round?->academic_year ?? '—') . '.',
                ],
                buttonLabel: 'Přejít do administrace',
                buttonUrl: route('admin.login'),
                metaLine: 'Číslo přihlášky: ' . ($application->application_number ?: $application->evidence_number ?: '#' . $application->id),
                fallbackUrl: route('admin.login'),
            ));
        } catch (\Throwable $e) {
            Log::error('Submitted application notification failed: ' . $e->getMessage(), [
                'application_id' => $application->id,
            ]);
        }
    }

    private function makeEvidenceNumber(Application $application): string
    {
        return 'EV' . now()->format('Y') . str_pad((string) $application->id, 5, '0', STR_PAD_LEFT);
    }

    private function birthNumberMatchesBirthDate(?string $birthNumber, $birthDate): bool
    {
        if (blank($birthNumber) || blank($birthDate)) {
            return true;
        }

        $digits = preg_replace('/\D+/', '', $birthNumber);
        if (! preg_match('/^\d{9,10}$/', $digits)) {
            return false;
        }

        $date = $birthDate instanceof \Carbon\CarbonInterface
            ? $birthDate
            : \Carbon\Carbon::parse($birthDate);

        $encodedYear = (int) substr($digits, 0, 2);
        $encodedMonth = (int) substr($digits, 2, 2);
        $encodedDay = (int) substr($digits, 4, 2);

        $validMonths = [
            $date->month,
            $date->month + 50,
        ];

        if ($date->year >= 2004) {
            $validMonths[] = $date->month + 20;
            $validMonths[] = $date->month + 70;
        }

        return $encodedYear === (int) $date->format('y')
            && $encodedDay === $date->day
            && in_array($encodedMonth, $validMonths, true);
    }
}
