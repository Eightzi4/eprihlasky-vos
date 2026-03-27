<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>E-přihláška | VOŠ OAUH</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .topo-bg {
            background-image: url("{{ asset('storage/topography_background.svg') }}");
            background-attachment: fixed;
            background-size: cover;
            background-position: center;
        }

        .material-symbols-rounded {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            line-height: 1;
            display: inline-flex;
            align-items: center;
            vertical-align: middle;
        }
    </style>
</head>

<body class="topo-bg bg-white text-school-dark antialiased flex flex-col min-h-screen relative">

    <div class="fixed inset-0 z-0 bg-white/5 backdrop-blur-[1px] pointer-events-none"></div>

    <header class="bg-[#f7f7f7]/90 backdrop-blur-md border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative h-16 flex items-center justify-between">
            <div class="flex items-center">
                <button type="button" onclick="navigateTo('dashboard')"
                    class="group relative flex items-center justify-center px-4 py-2 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 cursor-pointer border border-transparent hover:border-white/50 bg-transparent">
                    <div class="absolute inset-0 topo-bg opacity-30 transition-opacity duration-300"></div>
                    <div
                        class="absolute inset-0 bg-white/60 backdrop-blur-[2px] group-hover:backdrop-blur-[4px] transition-all duration-300">
                    </div>
                    <div class="absolute inset-0 rounded-xl border border-white/60 border-b-2 border-b-gray-200/50">
                    </div>
                    <span class="relative z-10 text-gray-600 font-bold text-xs flex items-center gap-2">
                        <span
                            class="material-symbols-rounded text-[18px] text-gray-500 group-hover:text-school-primary transition-colors duration-300">save</span>
                        Uložit a odejít
                    </span>
                </button>
            </div>

            <div class="flex items-center gap-3">
                @if (config('app.env') !== 'production')
                    <a href="{{ route('nia.mock.login', $application->id) }}"
                        class="group relative flex items-center justify-center px-3 py-1.5 rounded-lg overflow-hidden border border-dashed border-amber-400 bg-amber-50 hover:bg-amber-100 transition-all duration-200 cursor-pointer">
                        <span class="text-amber-700 font-bold text-xs flex items-center gap-1.5">
                            <span class="material-symbols-rounded text-[15px]">science</span>
                            Simulovat NIA
                        </span>
                    </a>
                @endif

                <div id="autosave-indicator" class="text-xs text-gray-400 font-mono transition-all duration-300">
                    ID: {{ $application->application_number ?? $application->id }}
                </div>
            </div>
        </div>
    </header>

    <main class="flex-grow w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 relative z-10">

        @php
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

        <div class="lg:hidden space-y-4 mb-6">
            <div
                class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-white/60 p-5 ring-1 ring-black/5">
                <div class="flex items-center gap-3 mb-4 border-b border-gray-100 pb-4">
                    <img src="https://www.oauh.cz/content/filters/l2.png" alt="Logo" class="h-7 w-auto">
                    <span class="text-sm font-bold text-gray-900 leading-tight">Obchodní akademie<br>Uherské
                        Hradiště</span>
                </div>
                <h3 class="text-base font-bold text-school-primary mb-0.5">
                    {{ $application->studyProgram->name ?? 'Studijní program' }}
                </h3>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-4">Vybraný studijní program</p>
                <div class="grid grid-cols-3 gap-3 text-xs">
                    <div>
                        <p class="text-gray-400 mb-0.5">Akademický rok</p>
                        <p class="font-semibold text-gray-900">{{ date('Y') }}/{{ date('Y') + 1 }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 mb-0.5">Forma studia</p>
                        <p class="font-semibold text-gray-900">{{ $application->studyProgram->form ?? 'Prezenční' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 mb-0.5">Školné</p>
                        <p class="font-semibold text-gray-900">{{ $application->studyProgram->tuition_fee ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <div
                class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-white/60 p-5 ring-1 ring-black/5">
                <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2 text-sm">
                    <span class="material-symbols-rounded text-gray-400 text-[18px]">rule</span>
                    Stav přihlášky
                </h3>

                <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">do {{ $fmtDate($deadline1) }}
                </p>
                <div class="grid grid-cols-2 gap-x-6 gap-y-2 text-xs font-medium mb-3">
                    @foreach ([[$s1, 'Osobní údaje', false], [$niaStatus, 'Ověření identity', true], [$gdprStatus, 'Souhlas s GDPR', false], [$submittedStatus, 'Přihláška odeslána', false]] as [$st, $lb, $indent])
                        <div class="flex items-center gap-2 {{ $indent ? 'pl-5' : '' }}">
                            <span
                                class="material-symbols-rounded {{ $statusIcon[$st]['cls'] }} text-[16px] flex-shrink-0">{{ $statusIcon[$st]['icon'] }}</span>
                            <span class="{{ $labelCls[$st] }}">{{ $lb }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="border-t border-dashed border-gray-200 my-3"></div>

                <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">do {{ $fmtDate($deadline2) }}
                </p>
                <div class="grid grid-cols-2 gap-x-6 gap-y-2 text-xs font-medium">
                    @foreach ([[$s2, 'Předchozí vzdělání', false], [$ps, 'Přihláška zaplacena', false]] as [$st, $lb, $indent])
                        <div class="flex items-center gap-2">
                            <span
                                class="material-symbols-rounded {{ $statusIcon[$st]['cls'] }} text-[16px] flex-shrink-0">{{ $statusIcon[$st]['icon'] }}</span>
                            <span class="{{ $labelCls[$st] }}">{{ $lb }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <x-step-nav :application="$application" :current="$currentStep" />

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

            <div class="lg:col-span-2 space-y-6">
                @yield('form-content')
            </div>

            <div class="hidden lg:block space-y-6 sticky top-24">

                <div
                    class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-white/60 p-6 ring-1 ring-black/5">
                    <div class="flex items-center gap-3 mb-6 border-b border-gray-100 pb-4">
                        <img src="https://www.oauh.cz/content/filters/l2.png" alt="Logo" class="h-8 w-auto">
                        <span class="text-sm font-bold text-gray-900 leading-tight">Obchodní akademie<br>Uherské
                            Hradiště</span>
                    </div>

                    <h3 class="text-lg font-bold text-school-primary mb-1">
                        {{ $application->studyProgram->name ?? 'Studijní program' }}
                    </h3>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-6">Vybraný studijní program</p>

                    <div class="space-y-4 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Akademický rok</span>
                            <span class="font-semibold text-gray-900">{{ date('Y') }}/{{ date('Y') + 1 }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Forma studia</span>
                            <span
                                class="font-semibold text-gray-900">{{ $application->studyProgram->form ?? 'Prezenční' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Školné</span>
                            <span
                                class="font-semibold text-gray-900">{{ $application->studyProgram->tuition_fee ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                @php
                    $step1Locked = $application->isStep1Locked();
                    $niaStatus = $step1Locked
                        ? 'locked'
                        : ($application->identity_verified
                            ? 'complete'
                            : 'incomplete');
                    $gdprStatus = $step1Locked ? 'locked' : ($application->gdpr_accepted ? 'complete' : 'incomplete');
                    $submittedStatus = $application->submitted ? 'complete' : 'incomplete';

                    $deadline1 = $application->deadline_at ?? \Carbon\Carbon::parse('2026-03-28');
                    $deadline2 = $application->education_locked_at ?? \Carbon\Carbon::parse('2026-05-04');
                    $fmtDate = fn($dt) => $dt->format('j. n. Y');
                @endphp

                <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-white/60 p-6 ring-1 ring-black/5"
                    x-data="statusPanel({
                        s1: '{{ $s1 }}',
                        s2: '{{ $s2 }}',
                        ps: '{{ $ps }}',
                        nia: '{{ $niaStatus }}',
                        gdpr: '{{ $gdprStatus }}',
                        submitted: '{{ $submittedStatus }}',
                        canSubmit: {{ $application->isStep1Complete() && $application->gdpr_accepted && !$application->submitted ? 'true' : 'false' }}
                    })" x-init="init()">
                    <h3 class="font-bold text-gray-900 mb-5 flex items-center gap-2">
                        <span class="material-symbols-rounded text-gray-400 text-[20px]">rule</span>
                        Stav přihlášky
                    </h3>

                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">
                        do {{ $fmtDate($deadline1) }}
                    </p>

                    <ul class="space-y-2.5 text-sm font-medium">
                        <li class="flex items-center gap-3">
                            <span
                                class="material-symbols-rounded text-[20px] flex-shrink-0 transition-colors duration-300"
                                :class="icon(s1).cls" x-text="icon(s1).icon"></span>
                            <span class="transition-colors duration-300" :class="lbl(s1)">Osobní údaje</span>
                        </li>
                        <li class="flex items-center gap-3 pl-7">
                            <span
                                class="material-symbols-rounded text-[18px] flex-shrink-0 transition-colors duration-300"
                                :class="icon(nia).cls" x-text="icon(nia).icon"></span>
                            <span class="text-xs transition-colors duration-300" :class="lbl(nia)">Ověření
                                identity</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span
                                class="material-symbols-rounded text-[20px] flex-shrink-0 transition-colors duration-300"
                                :class="icon(gdpr).cls" x-text="icon(gdpr).icon"></span>
                            <span class="transition-colors duration-300" :class="lbl(gdpr)">Souhlas s GDPR</span>
                        </li>
                    </ul>

                    <div class="mt-3 pt-3 border-t border-gray-100 flex items-center gap-3">
                        <span class="material-symbols-rounded text-[20px] flex-shrink-0 transition-colors duration-300"
                            :class="icon(submitted).cls" x-text="icon(submitted).icon"></span>
                        <span class="text-sm transition-colors duration-300"
                            :class="submitted === 'complete' ? 'text-gray-900 font-bold' : 'text-gray-500'">
                            Přihláška odeslána
                        </span>
                    </div>

                    <div class="my-5 border-t border-dashed border-gray-200"></div>

                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">
                        do {{ $fmtDate($deadline2) }}
                    </p>

                    <ul class="space-y-2.5 text-sm font-medium">
                        <li class="flex items-center gap-3">
                            <span
                                class="material-symbols-rounded text-[20px] flex-shrink-0 transition-colors duration-300"
                                :class="icon(s2).cls" x-text="icon(s2).icon"></span>
                            <span class="transition-colors duration-300" :class="lbl(s2)">Předchozí
                                vzdělání</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span
                                class="material-symbols-rounded text-[20px] flex-shrink-0 transition-colors duration-300"
                                :class="icon(ps).cls" x-text="icon(ps).icon"></span>
                            <span class="transition-colors duration-300" :class="lbl(ps)">Přihláška
                                zaplacena</span>
                        </li>
                    </ul>
                </div>

            </div>
        </div>
    </main>

    <script>
        const AUTOSAVE_URL = "{{ route('application.autosave', $application->id) }}";
        const STATUS_URL = "{{ route('application.status', $application->id) }}";
        const FILE_UPLOAD_URL = "{{ route('application.uploadAttachment', $application->id) }}";
        const FILE_DELETE_URL =
            "{{ route('application.deleteAttachment', ['id' => $application->id, 'attachmentId' => '__ID__']) }}";
        const CSRF_TOKEN = "{{ csrf_token() }}";
        const DASHBOARD_URL = "{{ route('dashboard') }}";

        function navigateTo(destination) {
            window.location.href = destination === 'dashboard' ? DASHBOARD_URL : destination;
        }

        function showAutosaveState(state, msg) {
            const el = document.getElementById('autosave-indicator');
            if (!el) return;
            const states = {
                saving: {
                    text: 'Ukládám…',
                    cls: 'text-gray-400'
                },
                saved: {
                    text: 'Uloženo ✓',
                    cls: 'text-green-600'
                },
                error: {
                    text: msg || 'Chyba při ukládání',
                    cls: 'text-red-500'
                },
                idle: {
                    text: 'ID: {{ $application->application_number ?? $application->id }}',
                    cls: 'text-gray-400'
                },
            };
            const s = states[state] || states.idle;
            el.textContent = s.text;
            el.className = `text-xs font-mono transition-all duration-300 ${s.cls}`;
            if (state === 'saved') setTimeout(() => showAutosaveState('idle'), 2000);
        }

        async function autosaveField(name, value) {
            showAutosaveState('saving');
            try {
                const res = await fetch(AUTOSAVE_URL, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        field: name,
                        value: value
                    }),
                });
                const data = await res.json();
                if (!res.ok) {
                    showAutosaveState('error', data.error || 'Chyba při ukládání');
                    window.dispatchEvent(new CustomEvent('autosave-error', {
                        detail: {
                            field: name,
                            message: data.error || 'Neplatná hodnota.'
                        }
                    }));
                } else {
                    showAutosaveState('saved');
                    window.dispatchEvent(new CustomEvent('autosave-ok', {
                        detail: {
                            field: name
                        }
                    }));
                }
            } catch {
                showAutosaveState('error', 'Chyba při ukládání');
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-autosave]').forEach(el => {
                const event = (el.tagName === 'SELECT') ? 'change' : 'blur';
                el.addEventListener(event, () => {
                    autosaveField(el.dataset.autosave, el.value);
                });
            });

            document.querySelectorAll('[data-autosave-checkbox]').forEach(el => {
                el.addEventListener('change', () => {
                    autosaveField(el.dataset.autosaveCheckbox, el.checked ? '1' : '0');
                });
            });
        });
        document.addEventListener('alpine:init', () => {
            Alpine.data('statusPanel', (initial) => ({
                s1: initial.s1,
                s2: initial.s2,
                ps: initial.ps,
                nia: initial.nia,
                gdpr: initial.gdpr,
                submitted: initial.submitted,
                canSubmit: initial.canSubmit,

                icons: {
                    complete: {
                        icon: 'check_circle',
                        cls: 'text-green-500'
                    },
                    incomplete: {
                        icon: 'error',
                        cls: 'text-orange-500'
                    },
                    locked: {
                        icon: 'lock',
                        cls: 'text-gray-400'
                    },
                    pending: {
                        icon: 'pending',
                        cls: 'text-blue-400'
                    },
                },
                labels: {
                    complete: 'text-gray-900',
                    incomplete: 'text-gray-500',
                    locked: 'text-gray-400',
                    pending: 'text-gray-700',
                },

                icon(status) {
                    return this.icons[status] || this.icons.incomplete;
                },
                lbl(status) {
                    return this.labels[status] || this.labels.incomplete;
                },

                async refresh() {
                    try {
                        const res = await fetch(STATUS_URL, {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': CSRF_TOKEN
                            }
                        });
                        const data = await res.json();
                        this.s1 = data.s1;
                        this.s2 = data.s2;
                        this.ps = data.ps;
                        this.nia = data.nia;
                        this.gdpr = data.gdpr;
                        this.submitted = data.submitted;
                        this.canSubmit = data.canSubmit;

                        window.dispatchEvent(new CustomEvent('status-updated', {
                            detail: data
                        }));
                    } catch (e) {}
                },

                init() {
                    window.addEventListener('autosave-ok', () => this.refresh());
                    window.addEventListener('file-uploaded', () => this.refresh());
                    window.addEventListener('file-deleted', () => this.refresh());
                },
            }));
        });
    </script>
</body>

</html>
