@extends('layouts.application')

@section('form-content')
    @php
        $maturitaFile = $application->attachments->where('type', 'maturita')->first();
        $serverErrors = array_keys($errors->toArray());
        $serverMessages = collect($errors->toArray())->map(fn($msgs) => $msgs[0])->toArray();
        $isLocked = $application->isStepLocked(2) || $application->prev_study_info_accepted;
        $isPending = $application->isStep2Complete() && $maturitaFile && !$application->prev_study_info_accepted;
    @endphp

    <div x-data="stepValidator({
        step: 2,
        serverErrorFields: @json($serverErrors),
        serverMessages: @json($serverMessages),
        fields: [
            { name: 'previous_school', message: 'Název střední školy je povinný.' },
            { name: 'izo', message: 'IZO školy je povinné.' },
            { name: 'school_type', message: 'Typ školy je povinný.' },
            { name: 'previous_study_field', message: 'Obor studia je povinný.' },
            { name: 'previous_study_field_code', message: 'Kód oboru je povinný.' },
            { name: 'graduation_year', message: 'Rok maturity je povinný.' },
            { name: 'grade_average', message: 'Průměr známek je povinný.' },
        ]
    })">

        @if ($isPending && !$isLocked)
            <div class="bg-blue-50 border border-blue-200 rounded-3xl p-5 flex items-center gap-4 mb-6">
                <span class="material-symbols-rounded text-blue-400 text-[28px] flex-shrink-0">hourglass_top</span>
                <div>
                    <p class="font-bold text-blue-900 text-sm">Čeká na uznání školou</p>
                    <p class="text-xs text-blue-700 mt-0.5">Vyplněné údaje o vzdělání čekají na ověření a uznání školou.</p>
                </div>
            </div>
        @endif

        @if ($application->prev_study_info_accepted)
            <div class="bg-green-50 border border-green-200 rounded-3xl p-5 flex items-center gap-4 mb-6">
                <span class="material-symbols-rounded text-green-500 text-[28px] flex-shrink-0">verified</span>
                <div>
                    <p class="font-bold text-green-900 text-sm">Vzdělání uznáno školou</p>
                    <p class="text-xs text-green-700 mt-0.5">Vaše údaje o předchozím vzdělání byly ověřeny a uznány.</p>
                </div>
            </div>
        @endif

        <x-form-section title="Předchozí vzdělání"
            description="Vyplňte údaje o střední škole a nahrajte maturitní vysvědčení.">
            <x-form-field name="previous_school" label="Název střední školy" icon="school" :span="2"
                :value="old('previous_school', $application->previous_school)" placeholder="Např. Obchodní akademie Uherské Hradiště" :locked="$isLocked" />
            <x-form-field name="izo" label="IZO školy" icon="pin" :value="old('izo', $application->izo)" placeholder="60371731"
                :locked="$isLocked" />
            <x-form-field name="school_type" label="Typ školy" icon="apartment" type="select" :options="[
                '' => 'Vyberte typ',
                'GYM' => 'Gymnázium',
                'SOŠ' => 'SOŠ (Střední odborná škola)',
                'SOU' => 'SOU (Střední odborné učiliště)',
                'Jiné' => 'Jiné',
            ]"
                :value="old('school_type', $application->school_type)" :locked="$isLocked" />
            <x-form-field name="previous_study_field" label="Obor studia" icon="menu_book" :value="old('previous_study_field', $application->previous_study_field)"
                placeholder="Např. Ekonomické lyceum" :locked="$isLocked" />
            <x-form-field name="previous_study_field_code" label="Kód oboru (KKOV)" icon="tag" :value="old('previous_study_field_code', $application->previous_study_field_code)"
                placeholder="18-20-M/01" :locked="$isLocked" />
            <x-form-field name="graduation_year" label="Rok maturity" icon="calendar_month" :value="old('graduation_year', $application->graduation_year)"
                placeholder="2025" :locked="$isLocked" />
            <x-form-field name="grade_average" label="Průměr známek" icon="functions" :value="old('grade_average', $application->grade_average)" placeholder="1.50"
                :locked="$isLocked" />
        </x-form-section>

        <div
            class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-sm border border-white/60 p-6 sm:p-8 ring-1 ring-black/5 mb-6">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">Maturitní vysvědčení</h2>
            <p class="text-sm text-gray-500 mb-6">Nahrajte sken maturitního vysvědčení (PDF, JPG, PNG).</p>
            <x-file-uploader field-name="maturita_file" :saved-files="$maturitaFile ? [$maturitaFile] : []" :locked="$isLocked" />
        </div>

        <x-step-footer :application="$application" prev-route="application.step1" prev-label="Zpět na osobní údaje"
            next-route="application.step3" />

    </div>
@endsection
