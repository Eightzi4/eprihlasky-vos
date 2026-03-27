<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\ApplicationRound;
use App\Models\StudyProgram;
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
            ->withCount(['applications', 'applicationRounds'])
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        return view('admin.rounds', compact('programs'));
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
        if ($studyProgram->applications()->exists()) {
            return redirect()->route('admin.rounds')
                ->with('error', 'Program nelze odstranit, protože už obsahuje přihlášky.');
        }

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
            'description' => ['nullable', 'string'],
            'image_path' => ['nullable', 'string', 'max:2048'],
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
            'description' => 'popis programu',
            'image_path' => 'obrázek programu',
        ];
    }

    private function normalizeProgramData(array $data): array
    {
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        foreach (['code', 'tuition_fee', 'description', 'image_path'] as $field) {
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
