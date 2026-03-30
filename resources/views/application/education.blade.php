@extends('layouts.application')

@section('form-content')
    @php
        $maturitaFile = $application->attachments->where('type', 'maturita')->first();
        $halfYearReportFile = $application->attachments->where('type', 'half_year_report')->first();
        $serverErrors = array_keys($errors->toArray());
        $serverMessages = collect($errors->toArray())->map(fn($msgs) => $msgs[0])->toArray();
        $isLocked = $application->isStepLocked(2) || $application->prev_study_info_accepted;
        $isPending = $application->isStep2Complete() && !$application->prev_study_info_accepted;
    @endphp

    <div x-data="{
        accepted: {{ $application->prev_study_info_accepted ? 'true' : 'false' }},
        pending: {{ $isPending ? 'true' : 'false' }},
        bringInPerson: {{ old('bring_maturita_in_person', $application->bring_maturita_in_person) ? 'true' : 'false' }}
    }" x-init="window.addEventListener('status-updated', e => {
        accepted = (e.detail.s2 === 'complete');
        pending = (e.detail.s2 === 'pending');
    })">

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
                { name: 'half_year_grade_average', message: 'Průměr známek za první pololetí 4. ročníku je povinný.' },
                { name: 'grade_average', message: 'Průměr známek za druhé pololetí 4. ročníku je povinný.' },
                { name: 'maturita_grade_average', message: 'Průměr známek z maturitního vysvědčení je povinný.' },
                { name: 'half_year_report_file', message: 'Vysvědčení ze 4. ročníku je povinné.' },
                { name: 'maturita_file', message: 'Notářsky ověřené maturitní vysvědčení je povinné.' },
            ]
        })">

            <div x-show="pending && !accepted" x-transition
                class="bg-blue-50 border border-blue-200 rounded-3xl p-5 flex items-center gap-4 mb-6">
                <span class="material-symbols-rounded text-blue-400 text-[28px] flex-shrink-0">hourglass_top</span>
                <div>
                    <p class="font-bold text-blue-900 text-sm">Čeká na uznání školou</p>
                    <p class="text-xs text-blue-700 mt-0.5">Vyplněné údaje o vzdělání čekají na ověření a uznání školou.</p>
                </div>
            </div>

            <div x-show="accepted" x-transition
                class="bg-green-50 border border-green-200 rounded-3xl p-5 flex items-center gap-4 mb-6">
                <span class="material-symbols-rounded text-green-500 text-[28px] flex-shrink-0">verified</span>
                <div>
                    <p class="font-bold text-green-900 text-sm">Vzdělání uznáno školou</p>
                    <p class="text-xs text-green-700 mt-0.5">Vaše údaje o předchozím vzdělání byly ověřeny a uznány.</p>
                </div>
            </div>

            <x-form-section title="Předchozí vzdělání"
                description="Vyplňte údaje o střední škole. Průměry známek počítejte bez známky z chování.">
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
            </x-form-section>

            <div class="space-y-6 mb-6">
                <div
                    class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-sm border border-white/60 p-6 sm:p-8 ring-1 ring-black/5">
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">Vysvědčení 4. ročník</h2>
                    <p class="text-sm text-gray-500 mb-2">Nahrajte vysvědčení ze 4. ročníku a doplňte průměry známek za obě
                        pololetí.</p>
                    <p class="text-sm text-gray-500 mb-6">Průměry známek počítejte bez známky z chování.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-form-field name="half_year_grade_average" label="Průměr známek za první pololetí"
                            icon="functions" :value="old('half_year_grade_average', $application->half_year_grade_average)" placeholder="1.50" :locked="$isLocked" />
                        <x-form-field name="grade_average" label="Průměr známek za druhé pololetí" icon="functions"
                            :value="old('grade_average', $application->grade_average)" placeholder="1.50" :locked="$isLocked" />
                    </div>

                    <x-file-uploader field-name="half_year_report_file" :saved-files="$halfYearReportFile ? [$halfYearReportFile] : []" :locked="$isLocked"
                        :required="true" required-message="Vysvědčení ze 4. ročníku je povinné." />
                </div>

                <div
                    class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-sm border border-white/60 p-6 sm:p-8 ring-1 ring-black/5">
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">Notářsky ověřené maturitní vysvědčení</h2>
                    <p class="text-sm text-gray-500 mb-6">Přiložte autorizovanou konverzi maturitního vysvědčení (tzn.
                        jeho digitální kopii, nikoli pouze doklad o provedené konverzi), nebo zvolte, že jej přinesete
                        osobně
                        do školy.</p>
                    <p class="text-sm text-gray-500 mb-6">Autorizovaná konverze je způsobem úředního ověření listiny
                        převedením do elektronické podoby a lze ji provést např. na kontaktních místech Czech POINT.</p>

                    <x-form-field name="maturita_grade_average" label="Průměr známek z maturitního vysvědčení"
                        icon="functions" :value="old('maturita_grade_average', $application->maturita_grade_average)" placeholder="1.50" :locked="$isLocked" />

                    <label class="flex items-start gap-4 cursor-pointer group mb-4">
                        <div class="relative flex items-center pt-1 flex-shrink-0">
                            <input type="checkbox" name="bring_maturita_in_person" value="1"
                                {{ old('bring_maturita_in_person', $application->bring_maturita_in_person) ? 'checked' : '' }}
                                x-model="bringInPerson" data-autosave-checkbox="bring_maturita_in_person"
                                @if ($isLocked) disabled @endif
                                class="peer h-6 w-6 cursor-pointer appearance-none rounded-md border-2 border-gray-300 bg-white transition-all checked:border-school-primary checked:bg-school-primary hover:border-school-primary disabled:cursor-not-allowed disabled:bg-gray-100">
                            <span
                                class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 mt-0.5 text-white opacity-0 peer-checked:opacity-100 pointer-events-none">
                                <span class="material-symbols-rounded text-[18px] font-bold">check</span>
                            </span>
                        </div>
                        <div class="text-sm text-gray-700 leading-relaxed">
                            <span class="font-bold text-gray-900 block mb-1">Přinesu do školy osobně</span>
                            Pokud tuto možnost zaškrtnete, nahrání maturitního vysvědčení není povinné.
                        </div>
                    </label>

                    <p x-show="bringInPerson" x-transition class="text-sm text-gray-500 mb-6" style="display: none;">
                        Nahrání maturitního vysvědčení není v tomto případě povinné.
                    </p>

                    <div x-show="!bringInPerson || {{ $maturitaFile ? 'true' : 'false' }}" x-transition
                        style="display: none;">
                        <x-file-uploader field-name="maturita_file" :saved-files="$maturitaFile ? [$maturitaFile] : []" :locked="$isLocked" :required="true"
                            required-message="Notářsky ověřené maturitní vysvědčení je povinné."
                            valid-when="bringInPerson" />
                    </div>
                </div>
            </div>

            <x-step-footer :application="$application" prev-route="application.step1" prev-label="Zpět na osobní údaje"
                next-route="application.step3" />

        </div>
    </div>
@endsection
