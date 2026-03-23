@extends('layouts.application')

@section('form-content')
    @php
        $maturitaFile = $application->attachments->where('type', 'maturita')->first();
        $otherFiles = $application->attachments->where('type', 'other');
        $paymentFile = $application->attachments->where('type', 'payment')->first();
        $canSubmit = $application->isStep1Complete() && $application->gdpr_accepted && !$application->submitted;
        $editBtn = fn(string $step) => route('application.' . $step, $application->id);
    @endphp

    <form id="main-form" action="{{ route('application.submit', $application->id) }}" method="POST">
        @csrf

        @include('application.summary._section', [
            'title' => 'Osobní a kontaktní údaje',
            'editRoute' => $editBtn('step1'),
            'locked' => false,
            'rows' => [
                [
                    'label' => 'Jméno a příjmení',
                    'value' => $application->first_name . ' ' . $application->last_name,
                ],
                ['label' => 'Pohlaví', 'value' => $application->gender],
                ['label' => 'Rodné číslo', 'value' => $application->birth_number, 'mono' => true],
                [
                    'label' => 'Datum a místo narození',
                    'value' =>
                        ($application->birth_date
                            ? \Carbon\Carbon::parse($application->birth_date)->format('d. m. Y')
                            : '—') .
                        ', ' .
                        $application->birth_city,
                ],
                ['label' => 'Státní občanství', 'value' => $application->citizenship],
                [
                    'label' => 'Adresa trvalého bydliště',
                    'value' =>
                        $application->street .
                        "\n" .
                        $application->zip .
                        ' ' .
                        $application->city .
                        "\n" .
                        $application->country,
                    'multiline' => true,
                ],
                ['label' => 'E-mail', 'value' => $application->email],
                ['label' => 'Telefon', 'value' => $application->phone],
            ],
            'file' => null,
            'fileLabel' => null,
            'otherFiles' => collect(),
        ])

        @include('application.summary._section', [
            'title' => 'Předchozí vzdělání',
            'editRoute' => $editBtn('step2'),
            'locked' => $application->prev_study_info_accepted,
            'rows' => array_filter([
                ['label' => 'Název střední školy', 'value' => $application->previous_school, 'span' => 2],
                ['label' => 'IZO školy', 'value' => $application->izo, 'mono' => true],
                ['label' => 'Typ školy', 'value' => $application->school_type],
                ['label' => 'Obor studia', 'value' => $application->previous_study_field],
                [
                    'label' => 'Kód oboru (KKOV)',
                    'value' => $application->previous_study_field_code,
                    'mono' => true,
                ],
                $application->graduation_year
                    ? ['label' => 'Rok maturity', 'value' => $application->graduation_year]
                    : null,
                $application->grade_average
                    ? ['label' => 'Průměr známek', 'value' => $application->grade_average]
                    : null,
            ]),
            'file' => $maturitaFile,
            'fileLabel' => 'Maturitní vysvědčení',
            'otherFiles' => collect(),
        ])

        @include('application.summary._section', [
            'title' => 'Doplňující informace',
            'editRoute' => $editBtn('step3'),
            'locked' => false,
            'rows' => [
                ['label' => 'Specifické potřeby', 'value' => $application->specific_needs ?: 'Neuvedeno'],
                ['label' => 'Poznámka', 'value' => $application->note ?: 'Bez poznámky'],
            ],
            'file' => null,
            'fileLabel' => null,
            'otherFiles' => $otherFiles,
        ])

        @include('application.summary._section', [
            'title' => 'Platba zápisného',
            'editRoute' => $editBtn('step4'),
            'locked' => $application->payment_accepted,
            'rows' => [
                [
                    'label' => 'Stav platby',
                    'value' => $application->payment_accepted
                        ? 'Přijata'
                        : ($application->paid
                            ? 'Čeká na ověření'
                            : 'Nezaplaceno'),
                ],
            ],
            'file' => $paymentFile,
            'fileLabel' => 'Potvrzení o platbě',
            'otherFiles' => collect(),
        ])

        @if (!$application->submitted)
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-200 shadow-sm mb-8 ring-1 ring-black/5">
                <label class="flex items-start gap-4 cursor-pointer group">
                    <div class="relative flex items-center pt-1 flex-shrink-0">
                        <input type="checkbox" name="consent" value="1"
                            {{ old('consent', $application->gdpr_accepted) ? 'checked' : '' }}
                            data-autosave-checkbox="gdpr_accepted"
                            class="peer h-6 w-6 cursor-pointer appearance-none rounded-md border-2 border-gray-300 bg-white transition-all checked:border-school-primary checked:bg-school-primary hover:border-school-primary">
                        <span
                            class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 mt-0.5 text-white opacity-0 peer-checked:opacity-100 pointer-events-none">
                            <span class="material-symbols-rounded text-[18px] font-bold">check</span>
                        </span>
                    </div>
                    <div class="text-sm text-gray-700 leading-relaxed">
                        <span class="font-bold text-gray-900 block mb-1">Potvrzuji správnost a pravdivost údajů</span>
                        Prohlašuji, že všechny uvedené údaje v této přihlášce jsou pravdivé a úplné. Jsem si vědom(a)
                        právních
                        následků uvedení nepravdivých údajů. Souhlasím se zpracováním osobních údajů pro účely přijímacího
                        řízení v souladu s GDPR.
                    </div>
                </label>
                @error('consent')
                    <div class="flex items-center gap-1 mt-2 ml-10 text-school-warning">
                        <span class="material-symbols-rounded text-[16px]">error</span>
                        <p class="text-xs font-medium">Musíte potvrdit souhlas se zpracováním údajů.</p>
                    </div>
                @enderror
            </div>
        @else
            <div class="bg-green-50 border border-green-200 rounded-3xl p-6 flex items-center gap-4 mb-8">
                <span class="material-symbols-rounded text-green-500 text-[32px] flex-shrink-0">check_circle</span>
                <div>
                    <p class="font-bold text-green-900">Přihláška byla odeslána</p>
                    <p class="text-sm text-green-700 mt-0.5">
                        Přihláška č. {{ $application->application_number }} byla úspěšně odeslána
                        {{ $application->submitted_at?->format('j. n. Y') }}.
                    </p>
                </div>
            </div>
        @endif

        <x-step-footer :application="$application" prev-route="application.step4" prev-label="Zpět na platbu" :submit-label="$application->submitted ? null : 'Odeslat přihlášku'"
            :submit-disabled="!$canSubmit" />

    </form>
@endsection
