@extends('layouts.admin')

@section('title', 'Detail přihlášky | Administrace OAUH')

@section('content')
    @php
        $maturitaFile = $application->attachments->where('type', 'maturita')->first();
        $paymentFile = $application->attachments->where('type', 'payment')->first();
        $otherFiles = $application->attachments->where('type', 'other');

        $statusIcon = [
            'complete' => ['icon' => 'check_circle', 'cls' => 'text-green-500'],
            'incomplete' => ['icon' => 'error', 'cls' => 'text-orange-500'],
            'locked' => ['icon' => 'lock', 'cls' => 'text-gray-400'],
            'pending' => ['icon' => 'pending', 'cls' => 'text-blue-400'],
        ];
        $labelCls = [
            'complete' => 'text-gray-900',
            'incomplete' => 'text-gray-500',
            'locked' => 'text-gray-400',
            'pending' => 'text-gray-700',
        ];

        $s1 = $application->step1Status();
        $s2 = $application->step2Status();
        $ps = $application->paymentStatus();
        $step1Locked = $application->isStep1Locked();
        $niaStatus = $step1Locked ? 'locked' : ($application->identity_verified ? 'complete' : 'incomplete');
        $gdprStatus = $step1Locked ? 'locked' : ($application->gdpr_accepted ? 'complete' : 'incomplete');
        $submittedStatus = $application->submitted ? 'complete' : 'incomplete';
        $deadline1 = $application->deadline_at ?? \Carbon\Carbon::parse('2026-03-28');
        $deadline2 = $application->education_locked_at ?? \Carbon\Carbon::parse('2026-05-04');
        $fmtDate = fn($dt) => $dt->format('j. n. Y');
    @endphp

    <div
        class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-sm border border-white/60 ring-1 ring-black/5 overflow-hidden mb-6">
        <div class="p-6 sm:p-8 flex items-center justify-between gap-6">
            <div class="min-w-0">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 leading-tight mb-1">
                    {{ $application->first_name }} {{ $application->last_name }}
                </h1>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-gray-500">
                    @if ($application->evidence_number)
                        <span class="flex items-center gap-1 font-bold text-school-primary">
                            <span class="material-symbols-rounded text-[15px]">badge</span>
                            {{ $application->evidence_number }}
                        </span>
                    @endif
                    @if ($application->application_number)
                        <span class="flex items-center gap-1 font-bold text-school-primary">
                            <span class="material-symbols-rounded text-[15px]">tag</span>
                            {{ $application->application_number }}
                        </span>
                    @endif
                    <span class="flex items-center gap-1">
                        <span class="material-symbols-rounded text-[15px]">schedule</span>
                        {{ $application->created_at->format('j. n. Y H:i') }}
                    </span>
                    @if ($application->submitted_at)
                        <span class="flex items-center gap-1 text-green-600 font-medium">
                            <span class="material-symbols-rounded text-[15px]">check_circle</span>
                            Odesláno {{ $application->submitted_at->format('j. n. Y H:i') }}
                        </span>
                    @endif
                    @if ($application->round)
                        <span class="flex items-center gap-1">
                            <span class="material-symbols-rounded text-[15px]">calendar_month</span>
                            {{ $application->round->label ?? $application->round->academic_year }}
                        </span>
                    @endif
                </div>
            </div>

            <a href="{{ route('admin.applications') }}"
                class="group relative flex-shrink-0 inline-flex items-center justify-center px-4 py-2 rounded-xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-300">
                <div class="absolute inset-0 topo-bg opacity-50 transition-opacity duration-300"></div>
                <div
                    class="absolute inset-0 bg-white/60 backdrop-blur-[2px] group-hover:backdrop-blur-[4px] transition-all duration-300">
                </div>
                <div class="absolute inset-0 rounded-xl border border-white/60 border-b-4 border-b-gray-200/50"></div>
                <span
                    class="relative z-10 text-gray-600 font-bold text-sm flex items-center drop-shadow-sm whitespace-nowrap">
                    <span
                        class="material-symbols-rounded mr-2 text-[18px] text-gray-600 group-hover:text-school-primary group-hover:-translate-x-1 transition-all duration-300">arrow_back</span>
                    Zpět na přehled
                </span>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 space-y-6">

            <div
                class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-sm border border-white/60 p-6 sm:p-8 ring-1 ring-black/5">
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-5">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 mb-1">Evidenční číslo</h2>
                        <p class="text-sm text-gray-500">Unikátní interní identifikátor přihlášky.</p>
                    </div>
                    <div class="text-sm font-bold text-school-primary">
                        Aktuálně: {{ $application->evidence_number ?? '—' }}
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.applications.evidence-number', $application->id) }}"
                    class="mt-6 flex flex-col sm:flex-row items-stretch sm:items-end gap-3">
                    @csrf
                    @method('PATCH')

                    <div class="flex-1">
                        <label for="evidence_number"
                            class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">
                            Evidenční číslo
                        </label>
                        <input id="evidence_number" name="evidence_number" type="text"
                            value="{{ old('evidence_number', $application->evidence_number) }}"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white/80 text-gray-900 font-semibold shadow-sm focus:outline-none focus:ring-2 focus:ring-school-primary/20 focus:border-school-primary">
                        @error('evidence_number')
                            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                        class="group relative inline-flex items-center justify-center px-6 py-3 rounded-xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-300 min-h-[52px]">
                        <div class="absolute inset-0 topo-bg opacity-50 transition-opacity duration-300"></div>
                        <div
                            class="absolute inset-0 bg-white/60 backdrop-blur-[2px] group-hover:backdrop-blur-[4px] transition-all duration-300">
                        </div>
                        <div class="absolute inset-0 rounded-xl border border-white/60 border-b-4 border-b-gray-200/50">
                        </div>
                        <span class="relative z-10 text-gray-900 font-bold text-sm flex items-center whitespace-nowrap">
                            <span
                                class="material-symbols-rounded mr-2 text-[18px] text-gray-500 group-hover:text-school-primary transition-colors">save</span>
                            Uložit číslo
                        </span>
                    </button>
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
                            {{ $application->street ?? '—' }}<br>
                            {{ $application->zip }} {{ $application->city }}<br>
                            {{ $application->country }}
                        </p>
                    </div>
                </div>
            </div>

            <div
                class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-sm border border-white/60 p-6 sm:p-8 ring-1 ring-black/5">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Předchozí vzdělání</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-10 gap-y-5">
                    <div class="sm:col-span-2">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">Název střední školy</p>
                        <p class="text-sm font-semibold text-gray-900">{{ $application->previous_school ?? '—' }}</p>
                    </div>
                    @foreach ([['label' => 'IZO školy', 'value' => $application->izo ?? '—', 'mono' => true], ['label' => 'Typ školy', 'value' => $application->school_type ?? '—'], ['label' => 'Obor studia', 'value' => $application->previous_study_field ?? '—'], ['label' => 'Kód oboru (KKOV)', 'value' => $application->previous_study_field_code ?? '—', 'mono' => true], ['label' => 'Rok maturity', 'value' => $application->graduation_year ?? '—'], ['label' => 'Průměr známek', 'value' => $application->grade_average ?? '—']] as $row)
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">{{ $row['label'] }}</p>
                            <p class="text-sm font-semibold text-gray-900 {{ $row['mono'] ?? false ? 'font-mono' : '' }}">
                                {{ $row['value'] }}</p>
                        </div>
                    @endforeach

                    <div class="sm:col-span-2 mt-1">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Maturitní vysvědčení</p>
                        @if ($maturitaFile)
                            <a href="{{ asset('storage/' . $maturitaFile->disk_path) }}" target="_blank"
                                class="inline-flex items-center gap-3 p-3 bg-white border border-gray-200 rounded-xl hover:shadow-md transition-all group/file">
                                <div
                                    class="h-10 w-10 bg-green-50 rounded-lg flex items-center justify-center text-green-600 border border-green-100 flex-shrink-0">
                                    <span class="material-symbols-rounded">description</span>
                                </div>
                                <div class="min-w-0">
                                    <p
                                        class="text-sm font-bold text-gray-900 group-hover/file:text-school-primary transition-colors">
                                        {{ $maturitaFile->filename }}</p>
                                    <p class="text-xs text-gray-400">{{ round($maturitaFile->size / 1024) }} KB &bull;
                                        Klikněte pro zobrazení</p>
                                </div>
                            </a>
                        @else
                            <div
                                class="inline-flex items-center gap-2 text-orange-700 bg-orange-50 px-4 py-2 rounded-xl border border-orange-100 text-sm font-bold">
                                <span class="material-symbols-rounded text-[18px]">warning</span>
                                Zatím nenahráno
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-6 pt-5 border-t border-gray-100 flex items-center justify-end gap-3">
                    @if ($application->isStep2Complete())
                        @if ($application->prev_study_info_accepted)
                            <div
                                class="flex items-center gap-2 text-green-700 bg-green-50 px-4 py-2 rounded-xl border border-green-100 text-sm font-bold">
                                <span class="material-symbols-rounded text-[18px]">verified</span>
                                Vzdělání uznáno
                            </div>
                            <form method="POST"
                                action="{{ route('admin.applications.revertEducation', $application->id) }}">
                                @csrf @method('PATCH')
                                <button type="submit"
                                    class="group relative flex items-center justify-center px-5 py-2.5 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 cursor-pointer">
                                    <div class="absolute inset-0 topo-bg opacity-40"></div>
                                    <div
                                        class="absolute inset-0 bg-white/60 backdrop-blur-[2px] group-hover:backdrop-blur-[4px] transition-all duration-300">
                                    </div>
                                    <div
                                        class="absolute inset-0 rounded-xl border border-white/60 border-b-2 border-b-gray-200/50">
                                    </div>
                                    <span class="relative z-10 text-gray-900 font-bold text-sm flex items-center">
                                        <span
                                            class="material-symbols-rounded mr-2 text-[18px] text-gray-500 group-hover:text-school-primary group-hover:-translate-x-0.5 transition-all duration-300">undo</span>
                                        Zrušit uznání
                                    </span>
                                </button>
                            </form>
                        @else
                            <form method="POST"
                                action="{{ route('admin.applications.acceptEducation', $application->id) }}">
                                @csrf @method('PATCH')
                                <button type="submit"
                                    class="group relative flex items-center justify-center px-5 py-2.5 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 cursor-pointer">
                                    <div class="absolute inset-0 topo-bg opacity-40"></div>
                                    <div
                                        class="absolute inset-0 bg-white/60 backdrop-blur-[2px] group-hover:backdrop-blur-[4px] transition-all duration-300">
                                    </div>
                                    <div
                                        class="absolute inset-0 rounded-xl border border-white/60 border-b-2 border-b-gray-200/50">
                                    </div>
                                    <span class="relative z-10 text-gray-900 font-bold text-sm flex items-center">
                                        <span
                                            class="material-symbols-rounded mr-2 text-[18px] text-gray-500 group-hover:text-school-primary transition-colors">check_circle</span>
                                        Uznat vzdělání
                                    </span>
                                </button>
                            </form>
                        @endif
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
                        <p class="text-sm text-gray-900 leading-relaxed">{{ $application->specific_needs ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">Poznámka</p>
                        <p class="text-sm text-gray-900 leading-relaxed">{{ $application->note ?: '—' }}</p>
                    </div>
                    @if ($otherFiles->isNotEmpty())
                        <div class="sm:col-span-2 mt-1">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Další přílohy</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach ($otherFiles as $file)
                                    <a href="{{ asset('storage/' . $file->disk_path) }}" target="_blank"
                                        class="flex items-center gap-3 p-3 bg-white border border-gray-200 rounded-xl hover:shadow-md transition-all group/file">
                                        <div
                                            class="h-10 w-10 bg-blue-50 rounded-lg flex items-center justify-center text-blue-600 border border-blue-100 flex-shrink-0">
                                            <span class="material-symbols-rounded">attach_file</span>
                                        </div>
                                        <div class="min-w-0">
                                            <p
                                                class="text-sm font-bold text-gray-900 truncate group-hover/file:text-school-primary transition-colors">
                                                {{ $file->filename }}</p>
                                            <p class="text-xs text-gray-400">{{ round($file->size / 1024) }} KB</p>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div
                class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-sm border border-white/60 p-6 sm:p-8 ring-1 ring-black/5">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Platba</h2>

                @if ($paymentFile)
                    <a href="{{ asset('storage/' . $paymentFile->disk_path) }}" target="_blank"
                        class="flex items-center gap-3 p-3 bg-white border border-gray-200 rounded-xl hover:shadow-md transition-all group/file mb-5">
                        <div
                            class="h-10 w-10 bg-green-50 rounded-lg flex items-center justify-center text-green-600 border border-green-100 flex-shrink-0">
                            <span class="material-symbols-rounded">receipt_long</span>
                        </div>
                        <div class="min-w-0">
                            <p
                                class="text-sm font-bold text-gray-900 truncate group-hover/file:text-school-primary transition-colors">
                                {{ $paymentFile->filename }}</p>
                            <p class="text-xs text-gray-400">{{ round($paymentFile->size / 1024) }} KB &bull; Zobrazit</p>
                        </div>
                    </a>
                @else
                    <div
                        class="inline-flex items-center gap-2 text-orange-700 bg-orange-50 px-4 py-2 rounded-xl border border-orange-100 text-sm font-bold mb-5">
                        <span class="material-symbols-rounded text-[18px]">warning</span>
                        Zatím nenahráno
                    </div>
                @endif

                <div class="pt-5 border-t border-gray-100 flex items-center justify-end gap-3">
                    @if ($paymentFile)
                        @if ($application->payment_accepted)
                            <div
                                class="flex items-center gap-2 text-green-700 bg-green-50 px-4 py-2 rounded-xl border border-green-100 text-sm font-bold">
                                <span class="material-symbols-rounded text-[18px]">verified</span>
                                Platba potvrzena
                            </div>
                            <form method="POST"
                                action="{{ route('admin.applications.revertPayment', $application->id) }}">
                                @csrf @method('PATCH')
                                <button type="submit"
                                    class="group relative flex items-center justify-center px-5 py-2.5 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 cursor-pointer">
                                    <div class="absolute inset-0 topo-bg opacity-40"></div>
                                    <div
                                        class="absolute inset-0 bg-white/60 backdrop-blur-[2px] group-hover:backdrop-blur-[4px] transition-all duration-300">
                                    </div>
                                    <div
                                        class="absolute inset-0 rounded-xl border border-white/60 border-b-2 border-b-gray-200/50">
                                    </div>
                                    <span class="relative z-10 text-gray-900 font-bold text-sm flex items-center">
                                        <span
                                            class="material-symbols-rounded mr-2 text-[18px] text-gray-500 group-hover:text-school-primary group-hover:-translate-x-0.5 transition-all duration-300">undo</span>
                                        Zrušit potvrzení
                                    </span>
                                </button>
                            </form>
                        @else
                            <form method="POST"
                                action="{{ route('admin.applications.acceptPayment', $application->id) }}">
                                @csrf @method('PATCH')
                                <button type="submit"
                                    class="group relative flex items-center justify-center px-5 py-2.5 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 cursor-pointer">
                                    <div class="absolute inset-0 topo-bg opacity-40"></div>
                                    <div
                                        class="absolute inset-0 bg-white/60 backdrop-blur-[2px] group-hover:backdrop-blur-[4px] transition-all duration-300">
                                    </div>
                                    <div
                                        class="absolute inset-0 rounded-xl border border-white/60 border-b-2 border-b-gray-200/50">
                                    </div>
                                    <span class="relative z-10 text-gray-900 font-bold text-sm flex items-center">
                                        <span
                                            class="material-symbols-rounded mr-2 text-[18px] text-gray-500 group-hover:text-school-primary transition-colors">check_circle</span>
                                        Potvrdit platbu
                                    </span>
                                </button>
                            </form>
                        @endif
                    @else
                        <span class="text-sm text-gray-400 font-medium">Žádný doklad k potvrzení</span>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <a href="{{ route('admin.applications.export.csv', $application->id) }}"
                    class="group relative flex items-center justify-center px-6 py-4 rounded-2xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-300 min-h-[72px]">
                    <div class="absolute inset-0 topo-bg opacity-50 transition-opacity duration-300"></div>
                    <div
                        class="absolute inset-0 bg-white/60 backdrop-blur-[2px] group-hover:backdrop-blur-[4px] transition-all duration-300">
                    </div>
                    <div class="absolute inset-0 rounded-2xl border border-white/60 border-b-4 border-b-gray-200/50"></div>
                    <span
                        class="relative z-10 text-gray-900 font-bold text-base flex items-center justify-center whitespace-nowrap">
                        <span
                            class="material-symbols-rounded mr-3 text-[22px] text-gray-500 group-hover:text-school-primary transition-colors">table_view</span>
                        Export CSV
                    </span>
                </a>

                <a href="{{ route('admin.applications.export.pdf', $application->id) }}"
                    class="group relative flex items-center justify-center px-6 py-4 rounded-2xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-300 min-h-[72px]">
                    <div class="absolute inset-0 topo-bg opacity-50 transition-opacity duration-300"></div>
                    <div
                        class="absolute inset-0 bg-white/60 backdrop-blur-[2px] group-hover:backdrop-blur-[4px] transition-all duration-300">
                    </div>
                    <div class="absolute inset-0 rounded-2xl border border-white/60 border-b-4 border-b-gray-200/50"></div>
                    <span
                        class="relative z-10 text-gray-900 font-bold text-base flex items-center justify-center whitespace-nowrap">
                        <span
                            class="material-symbols-rounded mr-3 text-[22px] text-gray-500 group-hover:text-school-primary transition-colors">picture_as_pdf</span>
                        Export PDF
                    </span>
                </a>
            </div>

        </div>

        <div class="space-y-6">
            <div class="sticky top-24 space-y-6">

                <div
                    class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-white/60 p-6 ring-1 ring-black/5">
                    <div class="flex items-center gap-3 mb-6 border-b border-gray-100 pb-4">
                        <img src="https://www.oauh.cz/content/filters/l2.png" alt="Logo" class="h-8 w-auto">
                        <span class="text-sm font-bold text-gray-900 leading-tight">Obchodní akademie<br>Uherské
                            Hradiště</span>
                    </div>
                    <h3 class="text-lg font-bold text-school-primary mb-1">
                        {{ $application->studyProgram->name ?? '—' }}
                    </h3>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-6">Vybraný studijní program</p>
                    <div class="space-y-4 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Akademický rok</span>
                            <span class="font-semibold text-gray-900">{{ date('Y') }}/{{ date('Y') + 1 }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Forma studia</span>
                            <span class="font-semibold text-gray-900">{{ $application->studyProgram->form ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Délka studia</span>
                            <span
                                class="font-semibold text-gray-900">{{ $application->studyProgram->length ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Školné</span>
                            <span
                                class="font-semibold text-gray-900">{{ $application->studyProgram->tuition_fee ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Udělovaný titul</span>
                            <span
                                class="font-semibold text-gray-900">{{ $application->studyProgram->degree ?? '—' }}</span>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-white/60 p-6 ring-1 ring-black/5">
                    <h3 class="font-bold text-gray-900 mb-5 flex items-center gap-2">
                        <span class="material-symbols-rounded text-gray-400 text-[20px]">rule</span>
                        Stav přihlášky
                    </h3>

                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">do {{ $fmtDate($deadline1) }}
                    </p>
                    <ul class="space-y-2.5 text-sm font-medium">
                        <li class="flex items-center gap-3">
                            <span
                                class="material-symbols-rounded {{ $statusIcon[$s1]['cls'] }} text-[20px] flex-shrink-0">{{ $statusIcon[$s1]['icon'] }}</span>
                            <span class="{{ $labelCls[$s1] }}">Osobní údaje</span>
                        </li>
                        <li class="flex items-center gap-3 pl-7">
                            <span
                                class="material-symbols-rounded {{ $statusIcon[$niaStatus]['cls'] }} text-[18px] flex-shrink-0">{{ $statusIcon[$niaStatus]['icon'] }}</span>
                            <span class="{{ $labelCls[$niaStatus] }} text-xs">Ověření identity</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span
                                class="material-symbols-rounded {{ $statusIcon[$gdprStatus]['cls'] }} text-[20px] flex-shrink-0">{{ $statusIcon[$gdprStatus]['icon'] }}</span>
                            <span class="{{ $labelCls[$gdprStatus] }}">Souhlas s GDPR</span>
                        </li>
                    </ul>

                    <div class="mt-3 pt-3 border-t border-gray-100 flex items-center gap-3">
                        <span
                            class="material-symbols-rounded {{ $statusIcon[$submittedStatus]['cls'] }} text-[20px] flex-shrink-0">{{ $statusIcon[$submittedStatus]['icon'] }}</span>
                        <span
                            class="{{ $application->submitted ? 'text-gray-900 font-bold' : 'text-gray-500' }} text-sm">Přihláška
                            odeslána</span>
                    </div>

                    <div class="my-5 border-t border-dashed border-gray-200"></div>

                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">do {{ $fmtDate($deadline2) }}
                    </p>
                    <ul class="space-y-2.5 text-sm font-medium">
                        <li class="flex items-center gap-3">
                            <span
                                class="material-symbols-rounded {{ $statusIcon[$s2]['cls'] }} text-[20px] flex-shrink-0">{{ $statusIcon[$s2]['icon'] }}</span>
                            <span class="{{ $labelCls[$s2] }}">Předchozí vzdělání</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span
                                class="material-symbols-rounded {{ $statusIcon[$ps]['cls'] }} text-[20px] flex-shrink-0">{{ $statusIcon[$ps]['icon'] }}</span>
                            <span class="{{ $labelCls[$ps] }}">Přihláška zaplacena</span>
                        </li>
                    </ul>
                </div>

            </div>
        </div>

    </div>
@endsection
