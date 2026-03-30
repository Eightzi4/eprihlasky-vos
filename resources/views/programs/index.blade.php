@extends('layouts.app')

@section('title', 'Studijní programy | VOŠ OAUH')

@section('header-left')
    <a href="{{ Auth::check() ? route('dashboard') : route('home') }}"
        class="group relative flex items-center justify-center px-4 py-2 rounded-xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-300">
        <div class="absolute inset-0 topo-bg opacity-50 transition-opacity duration-300"></div>
        <div
            class="absolute inset-0 bg-white/60 backdrop-blur-[2px] group-hover:backdrop-blur-[4px] transition-all duration-300">
        </div>
        <div class="absolute inset-0 rounded-xl border border-white/60 border-b-4 border-b-gray-200/50"></div>
        <span class="relative z-10 text-gray-600 font-bold text-sm flex items-center drop-shadow-sm">
            <span
                class="material-symbols-rounded mr-2 text-[18px] text-gray-600 group-hover:text-school-primary transition-transform duration-300 group-hover:-translate-x-1">arrow_back</span>
            Zpět
        </span>
    </a>
@endsection

@section('content')
    <div class="w-full max-w-6xl mx-auto">

        <div class="text-center mb-16">
            <div
                class="inline-block bg-white/80 backdrop-blur-xl rounded-3xl shadow-sm border border-white/60 ring-1 ring-black/5 px-10 py-8 max-w-2xl mx-auto">
                <div
                    class="inline-flex items-center px-3 py-1 rounded-full bg-gray-100 text-school-primary text-xs font-bold mb-5 border border-gray-200">
                    Akademický rok {{ date('Y') }}/{{ date('Y') + 1 }}
                </div>
                <h1 class="text-4xl sm:text-5xl font-bold text-gray-900 mb-4 leading-tight">
                    Nabídka studijních oborů
                </h1>
                <p class="text-gray-500 text-base leading-relaxed">
                    Vyberte si obor, který vás zajímá, a zahajte svou cestu k titulu DiS.
                </p>
            </div>
        </div>

        <div class="space-y-12">
            @foreach ($programs as $program)
                @php
                    $openRound = $program->openRound();
                    $nextRound = $program->nextRound();
                    $canApply = $openRound && !$openRound->isFull();

                    $displayRounds = collect([
                        $openRound ? ['round' => $openRound, 'role' => 'current'] : null,
                        $nextRound ? ['round' => $nextRound, 'role' => 'next'] : null,
                    ])
                        ->filter()
                        ->values();
                @endphp

                <div
                    class="group relative bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl overflow-hidden border border-white/60 ring-1 ring-black/5 flex flex-col lg:flex-row min-h-[500px] transition-all duration-500 hover:shadow-red-900/5">

                    <div class="relative w-full lg:w-2/5 overflow-hidden min-h-[300px] lg:min-h-full">
                        <img src="{{ $program->image_path }}" alt="{{ $program->name }}"
                            class="absolute inset-0 w-full h-full object-cover grayscale transition-all duration-700 group-hover:grayscale-0 group-hover:scale-105">
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent lg:bg-gradient-to-r lg:from-transparent lg:to-black/10">
                        </div>

                        @if ($nextRound && !$openRound)
                            <div class="absolute bottom-6 left-6 right-6 flex flex-wrap items-end justify-between gap-3">
                                <div class="flex flex-wrap gap-2">
                                    <span
                                        class="px-4 py-1.5 bg-white/95 backdrop-blur-md text-sm font-bold text-school-primary rounded-lg shadow-md border border-white/20">
                                        {{ $program->form }}
                                    </span>
                                    <span
                                        class="px-4 py-1.5 bg-gray-900/90 backdrop-blur-md text-sm font-bold text-white rounded-lg shadow-md border border-white/10">
                                        {{ $program->length }}
                                    </span>
                                </div>
                                <div
                                    class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gray-900/80 backdrop-blur-md shadow-lg">
                                    <span class="material-symbols-rounded text-amber-400 text-[18px]">schedule</span>
                                    <span class="text-white font-bold text-sm">Otevře
                                        {{ $nextRound->opens_at->format('j. n. Y') }}</span>
                                </div>
                            </div>
                        @elseif (!$openRound && !$nextRound)
                            <div class="absolute bottom-6 left-6 right-6 flex flex-wrap items-end justify-between gap-3">
                                <div class="flex flex-wrap gap-2">
                                    <span
                                        class="px-4 py-1.5 bg-white/95 backdrop-blur-md text-sm font-bold text-school-primary rounded-lg shadow-md border border-white/20">
                                        {{ $program->form }}
                                    </span>
                                    <span
                                        class="px-4 py-1.5 bg-gray-900/90 backdrop-blur-md text-sm font-bold text-white rounded-lg shadow-md border border-white/10">
                                        {{ $program->length }}
                                    </span>
                                </div>
                                <div
                                    class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gray-900/70 backdrop-blur-md shadow-lg">
                                    <span class="material-symbols-rounded text-gray-400 text-[18px]">block</span>
                                    <span class="text-white/70 font-bold text-sm">Přijímání uzavřeno</span>
                                </div>
                            </div>
                        @else
                            <div class="absolute bottom-6 left-6 right-6">
                                <div class="flex flex-wrap gap-2">
                                    <span
                                        class="px-4 py-1.5 bg-white/95 backdrop-blur-md text-sm font-bold text-school-primary rounded-lg shadow-md border border-white/20">
                                        {{ $program->form }}
                                    </span>
                                    <span
                                        class="px-4 py-1.5 bg-gray-900/90 backdrop-blur-md text-sm font-bold text-white rounded-lg shadow-md border border-white/10">
                                        {{ $program->length }}
                                    </span>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="w-full lg:w-3/5 p-8 sm:p-10 flex flex-col justify-between relative">

                        <div class="absolute top-0 right-0 p-10 opacity-[0.03] pointer-events-none">
                            <span
                                class="material-symbols-rounded text-[250px] text-school-primary leading-none">school</span>
                        </div>

                        <div>
                            <div class="mb-4">
                                <span class="text-sm font-bold tracking-wider text-gray-400 uppercase">Kód oboru:
                                    {{ $program->code }}</span>
                            </div>

                            <h2
                                class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4 group-hover:text-school-primary transition-colors">
                                {{ $program->name }}
                            </h2>

                            <p class="text-gray-600 text-base leading-relaxed mb-8 max-w-2xl">
                                {{ $program->description }}
                            </p>

                            <div
                                class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-5 mb-8 border-t border-b border-gray-200/60 py-7">
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">Udělovaný
                                        titul</span>
                                    <span class="text-base font-bold text-gray-900 flex items-center gap-2">
                                        <span class="material-symbols-rounded text-school-primary text-[20px]">school</span>
                                        {{ $program->degree }}
                                    </span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">Školné</span>
                                    <span class="text-base font-bold text-gray-900 flex items-center gap-2">
                                        <span
                                            class="material-symbols-rounded text-school-primary text-[20px]">payments</span>
                                        {{ $program->tuition_fee ?? '—' }}
                                    </span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">Místo
                                        výuky</span>
                                    <span class="text-base font-bold text-gray-900 flex items-center gap-2">
                                        <span
                                            class="material-symbols-rounded text-school-primary text-[20px]">location_on</span>
                                        {{ $program->location }}
                                    </span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">Jazyk
                                        výuky</span>
                                    <span class="text-base font-bold text-gray-900 flex items-center gap-2">
                                        <span
                                            class="material-symbols-rounded text-school-primary text-[20px]">translate</span>
                                        {{ $program->language }}
                                    </span>
                                </div>
                            </div>

                            @if ($displayRounds->isNotEmpty())
                                <div class="mb-8">
                                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">Přijímací kola
                                    </p>
                                    <div class="flex flex-col sm:flex-row gap-2">
                                        @foreach ($displayRounds as $item)
                                            @php
                                                $r = $item['round'];
                                                $role = $item['role'];

                                                if ($role === 'current') {
                                                    $totalSecs = max(
                                                        1,
                                                        $r->closes_at->timestamp - $r->opens_at->timestamp,
                                                    );
                                                    $elapsedSecs = now()->timestamp - $r->opens_at->timestamp;
                                                    $progress = min(
                                                        100,
                                                        max(0, round(($elapsedSecs / $totalSecs) * 100)),
                                                    );

                                                    $border = 'border-green-200';
                                                    $labelCls = 'text-green-600 bg-green-100';
                                                    $nameCls = 'text-green-900';
                                                    $dateCls = 'text-green-700';
                                                    $iconCls = 'text-green-500';
                                                    $doneColor = '#bbf7d0';
                                                    $remainColor = '#f0fdf4';
                                                    $chipText = 'Aktuální';
                                                } elseif ($role === 'next') {
                                                    $progress = null;
                                                    $border = 'border-amber-200';
                                                    $labelCls = 'text-amber-600 bg-amber-100';
                                                    $nameCls = 'text-amber-900';
                                                    $dateCls = 'text-amber-700';
                                                    $iconCls = 'text-amber-500';
                                                    $doneColor = null;
                                                    $remainColor = '#fffbeb';
                                                    $chipText = 'Nadcházející';
                                                } else {
                                                    $progress = 100;
                                                    $border = 'border-gray-200';
                                                    $labelCls = 'text-gray-400 bg-gray-100';
                                                    $nameCls = 'text-gray-400';
                                                    $dateCls = 'text-gray-400';
                                                    $iconCls = 'text-gray-300';
                                                    $doneColor = '#e5e7eb';
                                                    $remainColor = '#f9fafb';
                                                    $chipText = 'Předchozí';
                                                }

                                                $bgStyle =
                                                    $progress !== null && $doneColor
                                                        ? "background: linear-gradient(to right, {$doneColor} {$progress}%, {$remainColor} {$progress}%);"
                                                        : "background-color: {$remainColor};";
                                            @endphp

                                            <div class="relative flex flex-col justify-between px-4 py-3 rounded-xl border sm:flex-1 min-w-0 {{ $border }}"
                                                style="{{ $bgStyle }}">

                                                <div class="flex items-center justify-between gap-2 mb-1">
                                                    <span
                                                        class="text-xs font-bold {{ $labelCls }} px-2 py-0.5 rounded-lg whitespace-nowrap">
                                                        {{ $chipText }}
                                                    </span>
                                                </div>

                                                <p class="text-sm font-bold {{ $nameCls }} mb-1.5">
                                                    {{ $r->label ?? 'Kolo ' . $loop->iteration }}
                                                </p>

                                                <div class="flex items-center gap-1.5 {{ $dateCls }}">
                                                    <span
                                                        class="material-symbols-rounded text-[14px] {{ $iconCls }}">calendar_month</span>
                                                    <span class="text-xs font-semibold">
                                                        {{ $r->opens_at->format('j. n.') }} –
                                                        {{ $r->closes_at->format('j. n. Y') }}
                                                    </span>
                                                </div>

                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 mt-auto">
                            @if ($canApply)
                                <a href="{{ route('application.create', $program->id) }}"
                                    class="group/btn relative flex-grow flex items-center justify-center px-8 py-4 rounded-xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-300 cursor-pointer">
                                    <div class="absolute inset-0 topo-bg opacity-50 transition-opacity duration-300"></div>
                                    <div
                                        class="absolute inset-0 bg-white/60 backdrop-blur-[2px] group-hover/btn:backdrop-blur-[4px] transition-all duration-300">
                                    </div>
                                    <div
                                        class="absolute inset-0 rounded-xl border border-white/60 border-b-4 border-b-gray-200/50">
                                    </div>
                                    <span
                                        class="relative z-10 text-gray-900 font-bold text-base flex items-center drop-shadow-sm">
                                        Podat přihlášku
                                        <span
                                            class="material-symbols-rounded ml-3 text-[20px] text-gray-600 group-hover/btn:text-school-primary transition-transform duration-300 group-hover/btn:translate-x-1">arrow_forward</span>
                                    </span>
                                </a>
                            @elseif ($nextRound)
                                <div
                                    class="flex-grow flex items-center justify-center px-8 py-4 rounded-xl bg-gray-100 border border-gray-200 cursor-not-allowed">
                                    <span class="material-symbols-rounded text-amber-500 mr-3 text-[20px]">schedule</span>
                                    <span class="text-gray-500 font-bold text-sm">Přihlášky od
                                        {{ $nextRound->opens_at->format('j. n. Y') }}</span>
                                </div>
                            @else
                                <div
                                    class="flex-grow flex items-center justify-center px-8 py-4 rounded-xl bg-gray-100 border border-gray-200 cursor-not-allowed">
                                    <span class="material-symbols-rounded text-gray-400 mr-3 text-[20px]">block</span>
                                    <span class="text-gray-400 font-bold text-sm">Přijímání momentálně neprobíhá</span>
                                </div>
                            @endif

                            <a href="{{ $program->info_url ?: \App\Models\StudyProgram::DEFAULT_INFO_URL }}"
                                target="_blank" rel="noopener noreferrer"
                                class="flex items-center justify-center px-6 py-4 rounded-xl text-gray-500 font-semibold hover:text-school-primary hover:bg-white/50 transition-all whitespace-nowrap">
                                Více informací
                                <span class="material-symbols-rounded ml-2 text-[20px]">open_in_new</span>
                            </a>
                        </div>

                    </div>
                </div>
            @endforeach
        </div>

        @if ($programs->isEmpty())
            <div class="text-center py-24">
                <span class="material-symbols-rounded text-[64px] text-gray-300 mb-4 block">school</span>
                <p class="text-gray-400 font-medium">Momentálně nejsou k dispozici žádné studijní programy.</p>
            </div>
        @endif

    </div>
@endsection
