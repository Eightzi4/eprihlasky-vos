<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\StudyProgram;
use App\Models\ApplicationAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ApplicationController extends Controller
{
    private const AUTOSAVE_RULES = [
        'first_name'                => ['nullable', 'string', 'max:255'],
        'last_name'                 => ['nullable', 'string', 'max:255'],
        'gender'                    => ['nullable', 'string', 'in:Muž,Žena'],
        'birth_number'              => ['nullable', 'string', 'max:20', 'regex:/^\d{6}\/?(\d{3,4})$/'],
        'birth_date'                => ['nullable', 'date', 'before:today', 'after:1900-01-01'],
        'birth_city'                => ['nullable', 'string', 'max:255'],
        'citizenship'               => ['nullable', 'string', 'max:255'],
        'email'                     => ['nullable', 'email:rfc,dns', 'max:255'],
        'phone'                     => ['nullable', 'string', 'regex:/^(\+420)?\s?[1-9][0-9]{2}\s?[0-9]{3}\s?[0-9]{3}$/'],
        'street'                    => ['nullable', 'string', 'max:255'],
        'city'                      => ['nullable', 'string', 'max:255'],
        'zip'                       => ['nullable', 'string', 'regex:/^\d{3}\s?\d{2}$/'],
        'country'                   => ['nullable', 'string', 'max:255'],
        'previous_school'           => ['nullable', 'string', 'max:255'],
        'izo'                       => ['nullable', 'string', 'max:50'],
        'school_type'               => ['nullable', 'string', 'max:50'],
        'previous_study_field'      => ['nullable', 'string', 'max:255'],
        'previous_study_field_code' => ['nullable', 'string', 'max:50'],
        'graduation_year'           => ['nullable', 'digits:4', 'integer', 'min:1950', 'max:2030'],
        'grade_average'             => ['nullable', 'numeric', 'min:1.00', 'max:5.00'],
        'specific_needs'            => ['nullable', 'string'],
        'note'                      => ['nullable', 'string'],
        'gdpr_accepted'             => ['nullable', 'boolean'],
    ];

    private const AUTOSAVE_MESSAGES = [
        'value.regex'   => 'Hodnota není ve správném formátu.',
        'value.email'   => 'E-mail není platná adresa.',
        'value.digits'  => 'Musí být čtyřciferné číslo.',
        'value.min'     => 'Hodnota je příliš malá.',
        'value.max'     => 'Hodnota je příliš velká.',
        'value.numeric' => 'Musí být číslo.',
        'value.integer' => 'Musí být celé číslo.',
        'value.date'    => 'Neplatné datum.',
        'value.before'  => 'Datum musí být v minulosti.',
        'value.after'   => 'Datum je příliš staré.',
        'value.in'      => 'Neplatná hodnota.',
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

    public function programsIndex()
    {
        $programs = StudyProgram::where('is_active', true)->get();
        return view('programs.index', compact('programs'));
    }

    public function create($program_id)
    {
        $program = StudyProgram::findOrFail($program_id);

        $existingApp = Application::where('user_id', Auth::id())
            ->where('study_program_id', $program_id)
            ->where('submitted', false)
            ->first();

        if ($existingApp) {
            return redirect()->route('application.step1', $existingApp->id);
        }

        $app = Application::create([
            'user_id'          => Auth::id(),
            'study_program_id' => $program->id,
            'status'           => 'draft',
            'email'            => Auth::user()->email,
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

        if ($application->submitted && in_array($field, self::STEP1_FIELDS)) {
            return response()->json(['error' => 'Tato sekce je uzamčena.'], 403);
        }

        if ($application->submitted && $field === 'gdpr_accepted') {
            return response()->json(['error' => 'Souhlas s GDPR je již uzamčen.'], 403);
        }

        $verifiedFields = $application->verified_fields ?? [];
        if (in_array($field, self::NIA_LOCKED_FIELDS) && in_array($field, $verifiedFields)) {
            return response()->json(['error' => 'Toto pole bylo ověřeno pomocí NIA a nelze jej změnit.'], 403);
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
        if ($field === 'gdpr_accepted') {
            $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        $application->update([$field => $value]);
        $application->evaluateStates();

        return response()->json(['ok' => true]);
    }

    public function uploadAttachment(Request $request, $id)
    {
        $application = Application::where('user_id', Auth::id())->findOrFail($id);

        $fieldName = $request->input('field_name');
        $type      = match ($fieldName) {
            'maturita_file' => 'maturita',
            'other_files[]' => 'other',
            'payment_file'  => 'payment',
            default         => null,
        };

        if (! $type) {
            return response()->json(['message' => 'Neznámý typ souboru.'], 422);
        }

        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        if ($type === 'maturita' || $type === 'payment') {
            $old = $application->attachments()->where('type', $type)->first();
            if ($old) {
                Storage::disk('public')->delete($old->disk_path);
                $old->delete();
            }
        }

        $file       = $request->file('file');
        $path       = $file->store('applications/' . $application->id, 'public');
        $attachment = $application->attachments()->create([
            'type'      => $type,
            'filename'  => $file->getClientOriginalName(),
            'disk_path' => $path,
            'mime_type' => $file->getMimeType(),
            'size'      => $file->getSize(),
        ]);

        $application->evaluateStates();

        return response()->json([
            'ok'           => true,
            'attachmentId' => $attachment->id,
            'filename'     => $attachment->filename,
            'size'         => $attachment->size,
            'mime_type'    => $attachment->mime_type,
            'url'          => asset('storage/' . $attachment->disk_path),
        ]);
    }

    public function deleteAttachment(Request $request, $id, $attachmentId)
    {
        $attachment = ApplicationAttachment::where('application_id', $id)->findOrFail($attachmentId);
        $app        = $attachment->application;

        if ($app->user_id !== Auth::id()) {
            abort(403);
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
        return view('application.payment', ['application' => $application, 'currentStep' => 4]);
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

        $application->gdpr_accepted = $request->boolean('consent');
        $application->save();

        $request->validate([
            'consent' => 'accepted',
        ], [
            'consent.accepted' => 'Musíte potvrdit souhlas se zpracováním údajů.',
        ]);

        if (! $application->identity_verified) {
            return redirect()->route('application.step1', $id)
                ->with('error', 'Před odesláním přihlášky musíte ověřit svou identitu přes NIA.');
        }

        if (! $application->isStep1Complete()) {
            return redirect()->route('application.step1', $id)
                ->with('error', 'Před odesláním musíte vyplnit všechny povinné osobní údaje.');
        }

        $fieldErrors = [];
        $strictChecks = [
            'email'    => ['email:rfc', 'max:255'],
            'phone'    => ['regex:/^(\+420)?\s?[1-9][0-9]{2}\s?[0-9]{3}\s?[0-9]{3}$/'],
            'zip'      => ['regex:/^\d{3}\s?\d{2}$/'],
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

        $application->update([
            'submitted'          => true,
            'status'             => 'submitted',
            'submitted_at'       => now(),
            'application_number' => date('Y') . str_pad($application->id, 4, '0', STR_PAD_LEFT),
        ]);

        $application->evaluateStates();

        return redirect()->route('application.step5', $id)
            ->with('success', 'Přihláška byla úspěšně odeslána!');
    }
}
