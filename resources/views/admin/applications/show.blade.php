@extends('layouts.admin')

@section('title', 'Detail přihlášky | Administrace OAUH')

@section('content')
    @php
        $maturitaFile = $application->attachments->where('type', 'maturita')->first();
        $halfYearReportFile = $application->attachments->where('type', 'half_year_report')->first();
        $paymentFile = $application->attachments->where('type', 'payment')->first();
        $canAcceptEducation =
            $application->submitted && $application->isStep2Complete() && $halfYearReportFile && $maturitaFile;
        $canAcceptPayment = $application->submitted && $paymentFile;
        $educationBaseReady =
            collect([
                $application->previous_school,
                $application->izo,
                $application->school_type,
                $application->previous_study_field,
                $application->previous_study_field_code,
                $application->graduation_year,
                $application->grade_average,
                $application->half_year_grade_average,
                $application->maturita_grade_average,
            ])->every(fn($value) => filled($value)) && (bool) $halfYearReportFile;
        $statusIcon = [
            'complete' => ['icon' => 'check_circle', 'cls' => 'text-green-500'],
            'incomplete' => ['icon' => 'error', 'cls' => 'text-orange-500'],
            'locked' => ['icon' => 'lock', 'cls' => 'text-gray-400'],
            'pending' => ['icon' => 'pending', 'cls' => 'text-blue-400'],
            'failed' => ['icon' => 'cancel', 'cls' => 'text-school-primary'],
        ];
        $labelCls = [
            'complete' => 'text-gray-900',
            'incomplete' => 'text-gray-500',
            'locked' => 'text-gray-400',
            'pending' => 'text-gray-700',
            'failed' => 'text-red-700',
        ];
        $panel = $application->statusPanelData();
        $s1 = $panel['s1'];
        $s2 = $panel['s2'];
        $ps = $panel['ps'];
        $niaStatus = $panel['nia'];
        $gdprStatus = $panel['gdpr'];
        $submittedStatus = $panel['submitted'];
        $finalStatus = $panel['finalStatus'];
        $deadline1 = $application->submissionDeadlineAt();
        $deadline2 = $application->completionDeadlineAt();
        $fmtDate = fn($dt) => $dt?->format('j. n. Y') ?? '—';
        $adminDeleteTemplate = route('admin.applications.deleteEducationAttachment', [$application->id, '__ID__']);
    @endphp

    <div
        class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-sm border border-white/60 ring-1 ring-black/5 overflow-hidden mb-6">
        <div class="p-6 sm:p-8 flex items-center justify-between gap-6">
            <div class="min-w-0">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 leading-tight mb-1">{{ $application->first_name }}
                    {{ $application->last_name }}</h1>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-gray-500">
                    @if ($application->evidence_number)
                        <span class="flex items-center gap-1 font-bold text-school-primary"><span
                                class="material-symbols-rounded text-[15px]">badge</span>{{ $application->evidence_number }}</span>
                    @endif
                    @if ($application->application_number)
                        <span class="flex items-center gap-1 font-bold text-school-primary"><span
                                class="material-symbols-rounded text-[15px]">tag</span>{{ $application->application_number }}</span>
                    @endif
                    <span class="flex items-center gap-1"><span
                            class="material-symbols-rounded text-[15px]">schedule</span>{{ $application->created_at->format('j. n. Y H:i') }}</span>
                    @if ($application->submitted_at)
                        <span class="flex items-center gap-1 text-green-600 font-medium"><span
                                class="material-symbols-rounded text-[15px]">check_circle</span>Odesláno
                            {{ $application->submitted_at->format('j. n. Y H:i') }}</span>
                    @endif
                </div>
            </div>
            <x-button as="a" href="{{ route('admin.applications') }}"
                text="Zpět na přehled" icon="arrow_back" iconAnimation="back"
                size="sm" spanClass="text-gray-600"
                extraClass="flex-shrink-0 inline-flex" />
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="{ statusLegendOpen: false }">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-sm border border-white/60 p-6 ring-1 ring-black/5">
                <form method="POST" action="{{ route('admin.applications.evidence-number', $application->id) }}"
                    class="flex flex-col lg:flex-row lg:items-start gap-3">
                    @csrf
                    @method('PATCH')
                    <div class="flex-1 max-w-xl">
                        <label for="evidence_number"
                            class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Evidenční číslo</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-symbols-rounded text-gray-400 text-[20px]">tag</span>
                            </div>
                            <input id="evidence_number" name="evidence_number" type="text"
                                value="{{ old('evidence_number', $application->evidence_number) }}"
                                placeholder="EV-0000"
                                class="w-full rounded-xl border shadow-sm focus:outline-none focus:ring-2 focus:ring-school-primary focus:border-school-primary bg-white/50 pl-10 py-3 text-sm placeholder-gray-400 {{ $errors->has('evidence_number') ? 'border-school-warning ring-1 ring-school-warning/30' : 'border-gray-200' }}">
                        </div>
                        @error('evidence_number')
                            <div class="flex items-center gap-1 mt-1.5 ml-1 text-school-warning">
                                <span class="material-symbols-rounded text-[16px]">error</span>
                                <p class="text-xs font-medium">{{ $message }}</p>
                            </div>
                        @enderror
                    </div>
                    <x-button as="button" type="submit" text="Uložit číslo"
                        icon="save" size="lg"
                        extraClass="min-h-[52px] mt-6" />
                </form>
            </div>

            <div
                class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-sm border border-white/60 p-6 sm:p-8 ring-1 ring-black/5">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Osobní a kontaktní údaje</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-10 gap-y-5">
                    @foreach ([['label' => 'Jméno a příjmení', 'value' => trim("{$application->first_name} {$application->last_name}") ?: '—'], ['label' => 'Pohlaví', 'value' => $application->gender ?? '—'], ['label' => 'Rodné číslo', 'value' => $application->birth_number ?? '—', 'mono' => true], ['label' => 'Datum narození', 'value' => $application->birth_date?->format('j. n. Y') ?? '—'], ['label' => 'Místo narození', 'value' => $application->birth_city ?? '—'], ['label' => 'Státní občanství', 'value' => $application->citizenship ?? '—'], ['label' => 'E-mail', 'value' => $application->email ?? '—'], ['label' => 'Telefon', 'value' => $application->phone ?? '—']] as $row)
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">{{ $row['label'] }}</p>
                            <p class="text-sm font-semibold text-gray-900 {{ $row['mono'] ?? false ? 'font-mono' : '' }}">
                                {{ $row['value'] }}</p>
                        </div>
                    @endforeach
                    <div class="sm:col-span-2">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">Adresa trvalého bydliště</p>
                        <p class="text-sm font-semibold text-gray-900 leading-relaxed">
                            {{ $application->street ?? '—' }}<br>{{ $application->zip ?? '' }}
                            {{ $application->city ?? '' }}<br>{{ $application->country ?? '—' }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-sm border border-white/60 p-6 sm:p-8 ring-1 ring-black/5"
                x-data="{ hasMaturita: @js((bool) $maturitaFile), submitted: @js((bool) $application->submitted), educationBaseReady: @js($educationBaseReady) }" x-on:file-uploaded.window="hasMaturita = true"
                x-on:file-deleted.window="hasMaturita = false">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Předchozí vzdělání</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-10 gap-y-5">
                    <div class="sm:col-span-2">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">Název střední školy</p>
                        <p class="text-sm font-semibold text-gray-900">{{ $application->previous_school ?? '—' }}</p>
                    </div>
                    @foreach ([['label' => 'IZO školy', 'value' => $application->izo ?? '—', 'mono' => true], ['label' => 'Typ školy', 'value' => $application->school_type ?? '—'], ['label' => 'Obor studia', 'value' => $application->previous_study_field ?? '—'], ['label' => 'Kód oboru (KKOV)', 'value' => $application->previous_study_field_code ?? '—', 'mono' => true], ['label' => 'Rok maturity', 'value' => $application->graduation_year ?? '—']] as $row)
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">{{ $row['label'] }}</p>
                            <p class="text-sm font-semibold text-gray-900 {{ $row['mono'] ?? false ? 'font-mono' : '' }}">
                                {{ $row['value'] }}</p>
                        </div>
                    @endforeach
                    <div class="sm:col-span-2 grid grid-cols-1 xl:grid-cols-2 gap-4 mt-1 items-stretch">
                        <div class="rounded-2xl border border-gray-200 bg-white/70 p-4 h-full flex flex-col">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Vysvědčení 4. ročník</p>
                            <div class="space-y-3 mb-4 flex-1">
                                <div>
                                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wide mb-1">Průměr
                                        známek za první pololetí</p>
                                    <p class="text-sm font-semibold text-gray-900">
                                        {{ $application->half_year_grade_average ?? '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wide mb-1">Průměr
                                        známek za druhé pololetí</p>
                                    <p class="text-sm font-semibold text-gray-900">{{ $application->grade_average ?? '—' }}
                                    </p>
                                </div>
                            </div>
                            @if ($halfYearReportFile)
                                <a href="{{ route('admin.applications.attachments.download', [$application->id, $halfYearReportFile->id]) }}"
                                    target="_blank"
                                    class="inline-flex items-center gap-3 p-3 bg-white border border-gray-200 rounded-xl hover:shadow-md transition-all group/file w-full mt-auto">
                                    @if (str_starts_with($halfYearReportFile->mime_type, 'image/'))
                                        <div
                                            class="h-10 w-10 rounded-lg overflow-hidden border border-gray-100 flex-shrink-0">
                                            <img src="{{ asset('storage/' . $halfYearReportFile->disk_path) }}"
                                                alt="{{ $halfYearReportFile->filename }}"
                                                class="w-full h-full object-cover">
                                        </div>
                                    @else
                                        <div
                                            class="h-10 w-10 bg-green-50 rounded-lg flex items-center justify-center text-green-600 border border-green-100 flex-shrink-0">
                                            <span class="material-symbols-rounded">description</span>
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <p
                                            class="text-sm font-bold text-gray-900 group-hover:file:text-school-primary transition-colors">
                                            {{ $halfYearReportFile->filename }}</p>
                                        <p class="text-xs text-gray-400">{{ round($halfYearReportFile->size / 1024) }} KB •
                                            Klikněte pro zobrazení</p>
                                    </div>
                                </a>
                            @else
                                <div
                                    class="inline-flex items-center gap-2 text-orange-700 bg-orange-50 px-4 py-2 rounded-xl border border-orange-100 text-sm font-bold mt-auto">
                                    <span class="material-symbols-rounded text-[18px]">warning</span>Zatím nenahráno
                                </div>
                            @endif
                        </div>

                        <div class="rounded-2xl border border-gray-200 bg-white/70 p-4 h-full flex flex-col">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Notářsky ověřené
                                maturitní vysvědčení</p>
                            <div class="space-y-3 mb-4 flex-1">
                                <div>
                                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wide mb-1">Průměr
                                        známek z maturitního vysvědčení</p>
                                    <p class="text-sm font-semibold text-gray-900">
                                        {{ $application->maturita_grade_average ?? '—' }}</p>
                                </div>
                                @if ($application->bring_maturita_in_person)
                                    <p class="text-sm text-gray-500 leading-relaxed">Uchazeč označil, že dokument přinese
                                        osobně. Po převzetí jej můžete nahrát zde.</p>
                                @endif
                            </div>
                            @if ($application->bring_maturita_in_person && !$application->prev_study_info_accepted)
                                <div class="mt-auto">
                                    <x-file-uploader field-name="maturita_file" :saved-files="$maturitaFile ? [$maturitaFile] : []" :upload-url="route('admin.applications.uploadEducationAttachment', $application->id)"
                                        :delete-url-template="$adminDeleteTemplate" :csrf-token="csrf_token()" />
                                </div>
                            @elseif ($maturitaFile)
                                <a href="{{ route('admin.applications.attachments.download', [$application->id, $maturitaFile->id]) }}"
                                    target="_blank"
                                    class="inline-flex items-center gap-3 p-3 bg-white border border-gray-200 rounded-xl hover:shadow-md transition-all group/file w-full mt-auto">
                                    @if (str_starts_with($maturitaFile->mime_type, 'image/'))
                                        <div
                                            class="h-10 w-10 rounded-lg overflow-hidden border border-gray-100 flex-shrink-0">
                                            <img src="{{ asset('storage/' . $maturitaFile->disk_path) }}"
                                                alt="{{ $maturitaFile->filename }}" class="w-full h-full object-cover">
                                        </div>
                                    @else
                                        <div
                                            class="h-10 w-10 bg-green-50 rounded-lg flex items-center justify-center text-green-600 border border-green-100 flex-shrink-0">
                                            <span class="material-symbols-rounded">description</span>
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <p
                                            class="text-sm font-bold text-gray-900 group-hover:file:text-school-primary transition-colors">
                                            {{ $maturitaFile->filename }}</p>
                                        <p class="text-xs text-gray-400">{{ round($maturitaFile->size / 1024) }} KB •
                                            Klikněte pro zobrazení</p>
                                    </div>
                                </a>
                            @else
                                <div
                                    class="inline-flex items-center gap-2 text-orange-700 bg-orange-50 px-4 py-2 rounded-xl border border-orange-100 text-sm font-bold mt-auto">
                                    <span class="material-symbols-rounded text-[18px]">warning</span>Zatím nenahráno
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @if (! $application->prev_study_info_accepted && $application->isStep2Complete())
                    <div class="mt-6 pt-5 border-t border-gray-100">
                        @if ($application->education_admin_message)
                            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs font-bold text-amber-700 uppercase tracking-wide mb-1">Zpráva pro uchazeče (odeslána)</p>
                                        <p class="text-sm text-amber-800">{{ $application->education_admin_message }}</p>
                                    </div>
                                    <form method="POST" action="{{ route('admin.applications.educationMessage', $application->id) }}" class="flex-shrink-0">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="message" value="">
                                        <button type="submit"
                                            class="text-xs font-semibold text-amber-700 hover:text-red-600 transition-colors">
                                            Odstranit zprávu
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <form method="POST" action="{{ route('admin.applications.educationMessage', $application->id) }}" class="space-y-3">
                                @csrf @method('PATCH')
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide">
                                    Zpráva pro uchazeče
                                    <span class="text-gray-400 font-normal">— bude odeslána e-mailem</span>
                                </label>
                                <textarea name="message" rows="3"
                                    placeholder="např. Dokument je rozmazaný, prosím nahrajte jej znovu v lepší kvalitě."
                                    class="w-full rounded-xl border border-gray-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-school-primary focus:border-school-primary bg-white/50 px-4 py-3 text-sm placeholder-gray-400 resize-none"></textarea>
                                <x-button as="button" type="submit"
                                    text="Odeslat zprávu" icon="send"
                                    variant="secondary" size="sm" spanClass="text-gray-700" />
                            </form>
                        @endif
                    </div>
                @elseif ($application->education_admin_message)
                    <div class="mt-6 pt-5 border-t border-gray-100">
                        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4">
                            <p class="text-xs font-bold text-amber-700 uppercase tracking-wide mb-1">Zpráva pro uchazeče (odeslána)</p>
                            <p class="text-sm text-amber-800">{{ $application->education_admin_message }}</p>
                        </div>
                    </div>
                @endif

                <div class="mt-6 pt-5 border-t border-gray-100 flex items-center justify-end gap-3">
                    @if ($application->prev_study_info_accepted)
                        <div
                            class="flex items-center gap-2 text-green-700 bg-green-50 px-4 py-2 rounded-xl border border-green-100 text-sm font-bold">
                            <span class="material-symbols-rounded text-[18px]">verified</span>Vzdělání uznáno
                        </div>
                        <form method="POST"
                            action="{{ route('admin.applications.revertEducation', $application->id) }}">@csrf
                            @method('PATCH')<x-button as="button" type="submit"
                                text="Zrušit uznání" icon="undo" iconAnimation="back"
                                variant="secondary" size="md" />
                            </button></form>
                    @elseif ($educationBaseReady)
                        <template x-if="submitted && hasMaturita">
                            <form method="POST"
                                action="{{ route('admin.applications.acceptEducation', $application->id) }}">@csrf
                                @method('PATCH')<x-button as="button" type="submit"
                                    text="Uznat vzdělání" icon="check_circle"
                                    variant="secondary" size="md" /></form>
                        </template>
                        <template x-if="!submitted">
                            <span class="text-sm text-gray-400 font-medium">Před ověřením musí uchazeč nejprve odeslat
                                přihlášku.</span>
                        </template>
                        <template x-if="submitted && !hasMaturita">
                            <span class="text-sm text-orange-700 font-medium">Před uznáním je potřeba mít nahrané oba
                                dokumenty.</span>
                        </template>
                    @else
                        <span class="text-sm text-gray-400 font-medium">Vzdělání dosud nevyplněno</span>
                    @endif
                </div>
            </div>

            <div
                class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-sm border border-white/60 p-6 sm:p-8 ring-1 ring-black/5">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Doplňující informace</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-10 gap-y-5">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">Specifické potřeby</p>
                        <p class="text-sm text-gray-900 leading-relaxed break-words whitespace-pre-wrap">{{ $application->specific_needs ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">Poznámka</p>
                        <p class="text-sm text-gray-900 leading-relaxed break-words whitespace-pre-wrap">{{ $application->note ?: '—' }}</p>
                    </div>
                </div>
            </div>

            <div
                class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-sm border border-white/60 p-6 sm:p-8 ring-1 ring-black/5">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Platba</h2>
                @if ($paymentFile)
                    <a href="{{ route('admin.applications.attachments.download', [$application->id, $paymentFile->id]) }}"
                        target="_blank"
                        class="flex items-center gap-3 p-3 bg-white border border-gray-200 rounded-xl hover:shadow-md transition-all group/file mb-5">
                        @if (str_starts_with($paymentFile->mime_type, 'image/'))
                            <div class="h-10 w-10 rounded-lg overflow-hidden border border-gray-100 flex-shrink-0">
                                <img src="{{ asset('storage/' . $paymentFile->disk_path) }}"
                                    alt="{{ $paymentFile->filename }}" class="w-full h-full object-cover">
                            </div>
                        @else
                            <div
                                class="h-10 w-10 bg-green-50 rounded-lg flex items-center justify-center text-green-600 border border-green-100 flex-shrink-0">
                                <span class="material-symbols-rounded">receipt_long</span>
                            </div>
                        @endif
                        <div class="min-w-0">
                            <p
                                class="text-sm font-bold text-gray-900 truncate group-hover:file:text-school-primary transition-colors">
                                {{ $paymentFile->filename }}</p>
                            <p class="text-xs text-gray-400">{{ round($paymentFile->size / 1024) }} KB • Klikněte pro
                                zobrazení</p>
                        </div>
                    </a>
                @else
                    <div
                        class="inline-flex items-center gap-2 text-orange-700 bg-orange-50 px-4 py-2 rounded-xl border border-orange-100 text-sm font-bold mb-5">
                        <span class="material-symbols-rounded text-[18px]">warning</span>Zatím nenahráno
                    </div>
                @endif

                @if (! $application->payment_accepted && $paymentFile)
                    <div class="pt-5 border-t border-gray-100 mt-5">
                        @if ($application->payment_admin_message)
                            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs font-bold text-amber-700 uppercase tracking-wide mb-1">Zpráva pro uchazeče (odeslána)</p>
                                        <p class="text-sm text-amber-800">{{ $application->payment_admin_message }}</p>
                                    </div>
                                    <form method="POST" action="{{ route('admin.applications.paymentMessage', $application->id) }}" class="flex-shrink-0">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="message" value="">
                                        <button type="submit"
                                            class="text-xs font-semibold text-amber-700 hover:text-red-600 transition-colors">
                                            Odstranit zprávu
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <form method="POST" action="{{ route('admin.applications.paymentMessage', $application->id) }}" class="space-y-3">
                                @csrf @method('PATCH')
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide">
                                    Zpráva pro uchazeče
                                    <span class="text-gray-400 font-normal">— bude odeslána e-mailem</span>
                                </label>
                                <textarea name="message" rows="3"
                                    placeholder="např. Doklad o platbě je nečitelný, prosím nahrajte jej znovu."
                                    class="w-full rounded-xl border border-gray-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-school-primary focus:border-school-primary bg-white/50 px-4 py-3 text-sm placeholder-gray-400 resize-none"></textarea>
                                <x-button as="button" type="submit"
                                    text="Odeslat zprávu" icon="send"
                                    variant="secondary" size="sm" spanClass="text-gray-700" />
                            </form>
                        @endif
                    </div>
                @elseif ($application->payment_admin_message)
                    <div class="pt-5 border-t border-gray-100 mt-5">
                        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4">
                            <p class="text-xs font-bold text-amber-700 uppercase tracking-wide mb-1">Zpráva pro uchazeče (odeslána)</p>
                            <p class="text-sm text-amber-800">{{ $application->payment_admin_message }}</p>
                        </div>
                    </div>
                @endif

                <div class="pt-5 border-t border-gray-100 flex items-center justify-end gap-3">
                    @if ($paymentFile)
                        @if ($application->payment_accepted)
                            <div
                                class="flex items-center gap-2 text-green-700 bg-green-50 px-4 py-2 rounded-xl border border-green-100 text-sm font-bold">
                                <span class="material-symbols-rounded text-[18px]">verified</span>Platba potvrzena
                            </div>
                            <form method="POST"
                                action="{{ route('admin.applications.revertPayment', $application->id) }}">@csrf
                                @method('PATCH')<x-button as="button" type="submit"
                                    text="Zrušit potvrzení" icon="undo"
                                    iconAnimation="back" variant="secondary" size="md" /></form>
                        @elseif ($canAcceptPayment)
                            <form method="POST"
                                action="{{ route('admin.applications.acceptPayment', $application->id) }}">@csrf
                                @method('PATCH')<x-button as="button" type="submit"
                                    text="Potvrdit platbu" icon="check_circle"
                                    variant="secondary" size="md" /></form>
                        @elseif (!$application->submitted)
                            <span class="text-sm text-gray-400 font-medium">Před ověřením musí uchazeč nejprve odeslat
                                přihlášku.</span>
                        @else
                            <span class="text-sm text-gray-400 font-medium">Platbu zatím nelze potvrdit.</span>
                        @endif
                    @else
                        <span class="text-sm text-gray-400 font-medium">Žádný doklad k potvrzení</span>
                    @endif
                </div>
            </div>

            <div
                class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-sm border border-white/60 p-6 sm:p-8 ring-1 ring-black/5">
                <h2 class="text-xl font-bold text-gray-900 mb-5">Export dat</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <x-button as="a"
                        href="{{ route('admin.applications.export.csv', $application->id) }}"
                        text="Export CSV" icon="table_view"
                        variant="secondary" size="wide"
                        extraClass="min-h-[60px]" spanClass="text-gray-700" />

                    <x-button as="a"
                        href="{{ route('admin.applications.export.pdf', $application->id) }}"
                        text="Export PDF" icon="picture_as_pdf"
                        variant="secondary" size="wide"
                        extraClass="min-h-[60px]" spanClass="text-gray-700" />

                    <x-button as="button"
                        onclick="openModal('zip-export-modal')"
                        text="Exportovat ZIP" icon="folder_zip"
                        variant="secondary" size="wide"
                        extraClass="min-h-[60px]" spanClass="text-gray-700" />
                </div>
            </div>

            <div id="zip-export-modal" class="fixed inset-0 z-50 hidden">
                <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm" onclick="closeModal('zip-export-modal')"></div>
                <div class="relative min-h-screen flex items-center justify-center p-4">
                    <div class="bg-white/90 backdrop-blur-xl rounded-3xl shadow-2xl w-full max-w-md p-8 relative border border-white/60 ring-1 ring-black/5">
                        <button type="button" onclick="closeModal('zip-export-modal')"
                            class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                            <span class="material-symbols-rounded text-[24px]">close</span>
                        </button>
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Exportovat ZIP</h2>
                        <form method="POST" action="{{ route('admin.applications.export.zip', $application->id) }}" class="space-y-5">
                            @csrf
                            <p class="text-sm text-gray-500">Vyberte, co chcete zahrnout do ZIP archivu:</p>

                            @foreach ([
                                ['name' => 'csv', 'label' => 'CSV soubor', 'icon' => 'table_view'],
                                ['name' => 'pdf', 'label' => 'PDF soubor', 'icon' => 'picture_as_pdf'],
                                ['name' => 'education', 'label' => 'Doklady o vzdělání', 'icon' => 'school'],
                                ['name' => 'payment', 'label' => 'Potvrzení o platbě', 'icon' => 'receipt_long'],
                                ['name' => 'other', 'label' => 'Přílohy', 'icon' => 'attach_file'],
                            ] as $opt)
                                <label class="flex items-center gap-4 cursor-pointer group">
                                    <div class="relative flex items-center flex-shrink-0">
                                        <input type="checkbox" name="{{ $opt['name'] }}" value="1" checked
                                            class="peer h-6 w-6 cursor-pointer appearance-none rounded-md border-2 border-gray-300 bg-white transition-all checked:border-school-primary checked:bg-school-primary hover:border-school-primary">
                                        <span class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 text-white opacity-0 peer-checked:opacity-100 pointer-events-none">
                                            <span class="material-symbols-rounded text-[18px] font-bold">check</span>
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2 text-sm font-medium text-gray-700">
                                        <span class="material-symbols-rounded text-gray-400 text-[20px]">{{ $opt['icon'] }}</span>
                                        {{ $opt['label'] }}
                                    </div>
                                </label>
                            @endforeach

                            <x-button as="button" type="submit" text="Stáhnout ZIP"
                                icon="download" fullWidth size="wide" />
                        </form>
                    </div>
                </div>
            </div>

        </div>

        <div class="space-y-6">
            <div class="sticky top-24 space-y-6">
                <div
                    class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-white/60 p-6 ring-1 ring-black/5">
                    <div class="flex items-center gap-3 mb-6 border-b border-gray-100 pb-4"><img
                            src="https://www.oauh.cz/content/filters/l2.png" alt="Logo" class="h-8 w-auto"><span
                            class="text-sm font-bold text-gray-900 leading-tight">Obchodní akademie<br>Uherské
                            Hradiště</span></div>
                    <h3 class="text-lg font-bold text-school-primary mb-1">{{ $application->studyProgram->name ?? '—' }}
                    </h3>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-6">Vybraný studijní program</p>
                    <div class="space-y-4 text-sm">
                        <div class="flex justify-between gap-4"><span class="text-gray-500">Akademický rok</span><span
                                class="font-semibold text-gray-900">{{ date('Y') }}/{{ date('Y') + 1 }}</span></div>
                        <div class="flex justify-between gap-4"><span class="text-gray-500">Forma studia</span><span
                                class="font-semibold text-gray-900">{{ $application->studyProgram->form ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between gap-4"><span class="text-gray-500">Délka studia</span><span
                                class="font-semibold text-gray-900">{{ $application->studyProgram->length ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between gap-4"><span class="text-gray-500">Školné</span><span
                                class="font-semibold text-gray-900">{{ $application->studyProgram->tuition_fee ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between gap-4"><span class="text-gray-500">Udělovaný titul</span><span
                                class="font-semibold text-gray-900">{{ $application->studyProgram->degree ?? '—' }}</span>
                        </div>
                    </div>
                </div>
                <div
                    class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-white/60 p-6 ring-1 ring-black/5">
                    <div class="mb-5 flex items-center justify-between gap-3">
                        <h3 class="font-bold text-gray-900 flex items-center gap-2"><span
                                class="material-symbols-rounded text-gray-400 text-[20px]">rule</span>Stav přihlášky</h3>
                        <button type="button" @click="statusLegendOpen = true"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-full text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600"
                            aria-label="Vysvětlení stavů přihlášky">
                            <span class="material-symbols-rounded text-[18px]">help</span>
                        </button>
                    </div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">do {{ $fmtDate($deadline1) }}
                    </p>
                    <ul class="space-y-2.5 text-sm font-medium">
                        <li class="flex items-center gap-3"><span
                                class="material-symbols-rounded {{ $statusIcon[$s1]['cls'] }} text-[20px] flex-shrink-0">{{ $statusIcon[$s1]['icon'] }}</span><span
                                class="{{ $labelCls[$s1] }}">Osobní údaje</span></li>
                        <li class="flex items-center gap-3 pl-7"><span
                                class="material-symbols-rounded {{ $statusIcon[$niaStatus]['cls'] }} text-[18px] flex-shrink-0">{{ $statusIcon[$niaStatus]['icon'] }}</span><span
                                class="{{ $labelCls[$niaStatus] }} text-xs">Ověření identity</span></li>
                        <li class="flex items-center gap-3"><span
                                class="material-symbols-rounded {{ $statusIcon[$gdprStatus]['cls'] }} text-[20px] flex-shrink-0">{{ $statusIcon[$gdprStatus]['icon'] }}</span><span
                                class="{{ $labelCls[$gdprStatus] }}">Souhlas s GDPR</span></li>
                    </ul>
                    <div class="mt-3 pt-3 border-t border-dashed border-gray-200 flex items-center gap-3"><span
                            class="material-symbols-rounded {{ $statusIcon[$submittedStatus]['cls'] }} text-[20px] flex-shrink-0">{{ $statusIcon[$submittedStatus]['icon'] }}</span><span
                            class="{{ $submittedStatus === 'complete' ? 'text-gray-900 font-bold' : $labelCls[$submittedStatus] }} text-sm">Přihláška
                            odeslána</span></div>
                    <div class="my-5 border-t border-dashed border-gray-200"></div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">do {{ $fmtDate($deadline2) }}
                    </p>
                    <ul class="space-y-2.5 text-sm font-medium">
                        <li class="flex items-center gap-3"><span
                                class="material-symbols-rounded {{ $statusIcon[$s2]['cls'] }} text-[20px] flex-shrink-0">{{ $statusIcon[$s2]['icon'] }}</span><span
                                class="{{ $labelCls[$s2] }}">Předchozí vzdělání</span></li>
                        <li class="flex items-center gap-3"><span
                                class="material-symbols-rounded {{ $statusIcon[$ps]['cls'] }} text-[20px] flex-shrink-0">{{ $statusIcon[$ps]['icon'] }}</span><span
                                class="{{ $labelCls[$ps] }}">Přihláška zaplacena</span></li>
                    </ul>

                    @if ($finalStatus)
                        <div class="mt-4 pt-4 border-t border-dashed border-gray-200 flex items-center gap-3">
                            <span
                                class="material-symbols-rounded text-[20px] flex-shrink-0 {{ $finalStatus['tone'] === 'success' ? 'text-amber-500' : 'text-school-primary' }}">{{ $finalStatus['icon'] }}</span>
                            <span
                                class="text-sm {{ $finalStatus['tone'] === 'success' ? 'text-gray-900 font-bold' : 'text-red-700' }}">{{ $finalStatus['label'] }}</span>
                        </div>
                    @endif

                    @if ($application->completionDeadlinePassed() && !$application->applicantCompletionRequirementsMet())
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">Přesun do jiného kola
                            </p>

                            @if ($availableMoveRounds->isNotEmpty())
                                <form method="POST"
                                    action="{{ route('admin.applications.move-to-round', $application->id) }}"
                                    class="space-y-3">
                                    @csrf
                                    @method('PATCH')
                                    <div>
                                        <label for="target_round_id"
                                            class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Cílové
                                            kolo</label>
                                        <select id="target_round_id" name="target_round_id"
                                            class="w-full rounded-xl border border-gray-200 bg-white/80 px-4 py-3 text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-school-primary/20 focus:border-school-primary">
                                            @foreach ($availableMoveRounds as $round)
                                                <option value="{{ $round->id }}">
                                                    {{ $round->label ?: $round->academic_year }} · doplnění do
                                                    {{ $round->completion_deadline_at?->format('j. n. Y H:i') }}</option>
                                            @endforeach
                                        </select>
                                        @error('target_round_id')
                                            <div class="flex items-center gap-1 mt-1.5 ml-1 text-school-warning">
                                                <span class="material-symbols-rounded text-[16px]">error</span>
                                                <p class="text-xs font-medium">{{ $message }}</p>
                                            </div>
                                        @enderror
                                    </div>
                                    <x-button as="button" type="submit"
                                        text="Přesunout do vybraného kola" icon="redo"
                                        variant="secondary" size="md" />
                                </form>
                            @else
                                <p class="text-sm text-gray-500">Pro tuto přihlášku zatím není připravené žádné jiné
                                    přijímací kolo.</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
            @include('components.status-legend')
        </div>
    </div>

    <script>
        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }
        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }
    </script>
@endsection
