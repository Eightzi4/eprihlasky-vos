@extends('layouts.admin')

@section('title', 'Studijní Programy A Kola | Administrace OAUH')

@section('content')
    @php
        $programCreateErrors = $errors->programCreate;
        $programUpdateErrors = $errors->programUpdate;
        $roundCreateErrors = $errors->roundCreate;
        $roundUpdateErrors = $errors->roundUpdate;
        $openModal = session('open_modal');
    @endphp

    <div class="space-y-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach ([['label' => 'Studijní programy', 'value' => $programs->count(), 'icon' => 'school'], ['label' => 'Letošní přijímací kola', 'value' => $programs->sum('application_rounds_count'), 'icon' => 'event'], ['label' => 'Aktivní programy', 'value' => $programs->where('is_active', true)->count(), 'icon' => 'visibility']] as $stat)
                <div
                    class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-white/60 ring-1 ring-black/5 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">{{ $stat['label'] }}</p>
                        <span class="material-symbols-rounded text-gray-500 text-[22px]">{{ $stat['icon'] }}</span>
                    </div>
                    <p class="text-3xl font-bold text-gray-900">{{ $stat['value'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="flex justify-center">
            <x-button as="button" onclick="openModal('create-program')"
                text="Přidat studijní program" icon="add" iconAnimation="rotate"
                rounded="2xl" size="wide"
                extraClass="min-w-[320px]" />
        </div>

        <div class="space-y-8">
            @forelse ($programs as $program)
                @php
                    $createRoundModalId = 'create-round-program-' . $program->id;
                @endphp
                <div x-data="{
                    open: false,
                    sortColumn: 'id',
                    sortDirection: 'desc',
                    rounds: {{ Js::from(
                        $program->applicationRounds->map(function ($round) use ($program) {
                            $status = !$round->is_active
                                ? 'Skryté'
                                : ($round->isOpen()
                                    ? 'Aktuální'
                                    : ($round->isUpcoming()
                                        ? 'Nadcházející'
                                        : 'Předchozí'));
                            return [
                                'id' => $round->id,
                                'label' => $round->label ?: 'Bez názvu kola',
                                'academic_year' => $round->academic_year,
                                'opens_at' => optional($round->opens_at)->format('Y-m-d H:i'),
                                'opens_at_label' => optional($round->opens_at)->format('j. n. Y H:i'),
                                'closes_at' => optional($round->closes_at)->format('Y-m-d H:i'),
                                'closes_at_label' => optional($round->closes_at)->format('j. n. Y H:i'),
                                'completion_deadline_at' => optional($round->completion_deadline_at)->format('Y-m-d H:i'),
                                'completion_deadline_at_label' => optional($round->completion_deadline_at)->format('j. n. Y H:i'),
                                'max_applicants' => $round->max_applicants,
                                'capacity_label' => $round->max_applicants ? (string) $round->max_applicants : 'Bez limitu',
                                'status' => $status,
                                'status_class' => !$round->is_active
                                    ? 'bg-gray-100 text-gray-500 border-gray-200'
                                    : ($round->isOpen()
                                        ? 'bg-green-50 text-green-700 border-green-200'
                                        : ($round->isUpcoming()
                                            ? 'bg-amber-50 text-amber-700 border-amber-200'
                                            : 'bg-gray-100 text-gray-400 border-gray-200')),
                                'edit_modal' => 'edit-round-' . $round->id,
                                'delete_url' => route('admin.application-rounds.destroy', $round),
                            ];
                        }),
                    ) }},
                    get sortedRounds() {
                        const direction = this.sortDirection === 'asc' ? 1 : -1;
                        return [...this.rounds].sort((a, b) => {
                            const av = this.sortValue(a, this.sortColumn);
                            const bv = this.sortValue(b, this.sortColumn);
                            if (typeof av === 'string' && typeof bv === 'string') {
                                return av.localeCompare(bv, 'cs') * direction;
                            }
                            return ((av < bv) ? -1 : (av > bv ? 1 : 0)) * direction;
                        });
                    },
                    sortValue(round, column) {
                        if (column === 'max_applicants') return round.max_applicants ?? Number.MAX_SAFE_INTEGER;
                        return round[column];
                    },
                    sortBy(column) {
                        this.sortDirection = this.sortColumn === column ?
                            (this.sortDirection === 'asc' ? 'desc' : 'asc') :
                            'asc';
                        this.sortColumn = column;
                    },
                    sortIcon(column) {
                        if (this.sortColumn !== column) return 'unfold_more';
                        return this.sortDirection === 'asc' ? 'keyboard_arrow_up' : 'keyboard_arrow_down';
                    }
                }"
                    class="group relative bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl overflow-hidden border border-white/60 ring-1 ring-black/5 flex flex-col transition-all duration-500 hover:shadow-red-900/5">

                    <div class="flex flex-col lg:flex-row lg:items-start">

                        <div
                            class="relative w-full lg:w-[42%] h-[380px] lg:h-[540px] lg:self-start lg:flex-shrink-0 overflow-hidden rounded-t-3xl lg:rounded-l-3xl lg:rounded-tr-none">
                            <img src="{{ $program->image_path }}" alt="{{ $program->name }}"
                                class="absolute inset-0 w-full h-full object-cover grayscale transition-all duration-700 group-hover:grayscale-0 group-hover:scale-105">
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent lg:bg-gradient-to-r lg:from-transparent lg:to-black/10">
                            </div>

                            <div class="absolute top-6 left-6 flex flex-wrap gap-2">
                                <span
                                    class="px-4 py-1.5 bg-white/95 backdrop-blur-md text-sm font-bold text-school-primary rounded-lg shadow-md border border-white/20">
                                    {{ $program->form }}
                                </span>
                                <span
                                    class="px-4 py-1.5 bg-gray-900/90 backdrop-blur-md text-sm font-bold text-white rounded-lg shadow-md border border-white/10">
                                    {{ $program->length }}
                                </span>
                            </div>

                            <div class="absolute bottom-6 left-6">
                                <div
                                    class="flex items-center gap-2 px-4 py-2.5 rounded-xl {{ $program->is_active ? 'bg-gray-900/80' : 'bg-gray-900/60' }} backdrop-blur-md shadow-lg">
                                    <span
                                        class="material-symbols-rounded {{ $program->is_active ? 'text-green-400' : 'text-gray-400' }} text-[18px]">
                                        {{ $program->is_active ? 'visibility' : 'visibility_off' }}
                                    </span>
                                    <span
                                        class="text-white font-bold text-sm">{{ $program->is_active ? 'Program je zobrazený' : 'Program je skrytý' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="w-full lg:w-[58%] p-8 sm:p-10 flex flex-col justify-between relative">
                            <div class="absolute top-0 right-0 p-10 opacity-[0.03] pointer-events-none">
                                <span
                                    class="material-symbols-rounded text-[250px] text-school-primary leading-none">school</span>
                            </div>

                            <div>
                                <div class="mb-4">
                                    <span class="text-sm font-bold tracking-wider text-gray-400 uppercase">Kód oboru:
                                        {{ $program->code ?: '—' }}</span>
                                </div>

                                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-4">
                                    <h2
                                        class="text-3xl sm:text-4xl font-bold text-gray-900 group-hover:text-school-primary transition-colors">
                                        {{ $program->name }}
                                    </h2>

                                    <div class="flex items-center gap-2">
                                        <x-button as="button" onclick="openModal('edit-program-{{ $program->id }}')"
                                            icon="edit" variant="ghost" size="icon-only"
                                            extraClass="w-11 h-11" />

                                        <form method="POST" action="{{ route('admin.programs.destroy', $program) }}"
                                            onsubmit="return confirm('Opravdu chcete odstranit tento studijní program?');">
                                            @csrf
                                            @method('DELETE')
                                            <x-button as="button" type="submit" icon="delete"
                                                variant="ghost" size="icon-only"
                                                extraClass="w-11 h-11"
                                                :disabled="$program->applications_count > 0" />
                                        </form>
                                    </div>
                                </div>

                                <p class="text-gray-600 text-base leading-relaxed mb-8 max-w-2xl">
                                    {{ $program->description ?: 'Program zatím nemá doplněný popis.' }}
                                </p>

                                <div
                                    class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-5 mb-8 border-t border-b border-gray-200/60 py-7">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">Udělovaný
                                            titul</span>
                                        <span class="text-base font-bold text-gray-900 flex items-center gap-2">
                                            <span
                                                class="material-symbols-rounded text-school-primary text-[20px]">school</span>
                                            {{ $program->degree }}
                                        </span>
                                    </div>
                                    <div class="flex flex-col">
                                        <span
                                            class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">Školné</span>
                                        <span class="text-base font-bold text-gray-900 flex items-center gap-2">
                                            <span
                                                class="material-symbols-rounded text-school-primary text-[20px]">payments</span>
                                            {{ $program->tuition_fee ?: '—' }}
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
                            </div>

                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 mt-auto">
                                <x-button as="button" click="open = !open"
                                    size="wide" extraClass="flex-grow" spanClass="gap-3">
                                    <span x-text="open ? 'Skrýt přijímací kola' : 'Zobrazit přijímací kola'"></span>
                                    <span class="material-symbols-rounded text-[20px] text-gray-600 group-hover/btn:text-school-primary transition-transform duration-300"
                                        :class="open ? 'rotate-180' : ''">expand_more</span>
                                </x-button>
                            </div>
                        </div>

                    </div>

                    <div x-show="open" class="px-8 pb-8 pt-6 border-t border-gray-200/60 space-y-4">
                        <div class="flex justify-end">
                            <x-button as="button" onclick="openModal('{{ $createRoundModalId }}')"
                                text="Přidat kolo" icon="add" iconAnimation="rotate"
                                variant="ghost" size="md" />
                        </div>

                        <div class="overflow-x-auto border border-gray-200/60 rounded-2xl bg-white shadow-sm">
                            <table class="w-full min-w-full divide-y divide-gray-100">
                                <thead class="bg-gray-50/50">
                                    <tr>
                                        @foreach ([['id' => 'id', 'label' => 'ID'], ['id' => 'label', 'label' => 'Kolo'], ['id' => 'academic_year', 'label' => 'Akademický rok'], ['id' => 'opens_at', 'label' => 'Otevření'], ['id' => 'closes_at', 'label' => 'Uzavření'], ['id' => 'max_applicants', 'label' => 'Kapacita'], ['id' => 'status', 'label' => 'Stav']] as $th)
                                            <th class="px-5 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 select-none transition-colors"
                                                @click="sortBy('{{ $th['id'] }}')">
                                                <div class="flex items-center gap-1">
                                                    {{ $th['label'] }}
                                                    <span class="material-symbols-rounded text-[16px]"
                                                        x-text="sortIcon('{{ $th['id'] }}')"></span>
                                                </div>
                                            </th>
                                        @endforeach
                                        <th
                                            class="px-5 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            Dokončení­</th>
                                        <th
                                            class="px-5 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            Akce</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    <template x-if="sortedRounds.length === 0">
                                        <tr>
                                            <td colspan="9" class="px-6 py-12 text-center text-gray-400">
                                                K tomuto programu zatím není přiřazeno žádné přijímací kolo.
                                            </td>
                                        </tr>
                                    </template>
                                    <template x-for="round in sortedRounds" :key="round.id">
                                        <tr class="hover:bg-red-50/30 transition-colors">
                                            <td class="px-5 py-4 whitespace-nowrap text-sm font-bold text-gray-500 font-mono"
                                                x-text="`#${round.id}`"></td>
                                            <td class="px-5 py-4 text-sm font-bold text-gray-900" x-text="round.label">
                                            </td>
                                            <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-500"
                                                x-text="round.academic_year"></td>
                                            <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-500"
                                                x-text="round.opens_at_label"></td>
                                            <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-500"
                                                x-text="round.closes_at_label"></td>
                                            <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-500"
                                                x-text="round.capacity_label"></td>
                                            <td class="px-5 py-4 whitespace-nowrap text-sm">
                                                <span
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border"
                                                    :class="round.status_class">
                                                    <span x-text="round.status"></span>
                                                </span>
                                            </td>
                                            <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-500"
                                                x-text="round.completion_deadline_at_label"></td>
                                            <td class="px-5 py-4 whitespace-nowrap">
                                                <div class="flex items-center gap-2">
                                                    <x-button as="button" click="openModal(round.edit_modal)"
                                                        icon="edit" variant="ghost" size="icon-only" />

                                                    <form method="POST" :action="round.delete_url"
                                                        onsubmit="return confirm('Opravdu chcete odstranit toto přijímací kolo?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <x-button as="button" type="submit" icon="delete"
                                                            variant="ghost" size="icon-only" />
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            @empty
                <div
                    class="text-center py-24 bg-white/70 backdrop-blur-xl rounded-3xl border border-white/60 ring-1 ring-black/5">
                    <span class="material-symbols-rounded text-[64px] text-gray-300 mb-4 block">school</span>
                    <p class="text-gray-400 font-medium">Momentálně nejsou vytvořeny žádné studijní programy.</p>
                </div>
            @endforelse
        </div>
    </div>

    <div id="create-program" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm" onclick="closeModal('create-program')"></div>
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div
                class="bg-white/90 backdrop-blur-xl rounded-3xl shadow-2xl w-full max-w-4xl p-8 relative border border-white/60 ring-1 ring-black/5 max-h-[90vh] overflow-y-auto">
                <button type="button" onclick="closeModal('create-program')"
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                    <span class="material-symbols-rounded text-[24px]">close</span>
                </button>
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Nový studijní program</h2>
                <form action="{{ route('admin.programs.store') }}" method="POST" class="space-y-6">
                    @csrf
                    @include('admin.partials.program-form', [
                        'errorsBag' => $programCreateErrors,
                        'prefix' => 'program-create',
                        'program' => null,
                        'submitLabel' => 'Vytvořit program',
                    ])
                </form>
            </div>
        </div>
    </div>

    @foreach ($programs as $program)
        @php
            $createRoundModalId = 'create-round-program-' . $program->id;
        @endphp
        <div id="{{ $createRoundModalId }}" class="fixed inset-0 z-50 hidden">
            <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm"
                onclick="closeModal('{{ $createRoundModalId }}')"></div>
            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div
                    class="bg-white/90 backdrop-blur-xl rounded-3xl shadow-2xl w-full max-w-3xl p-8 relative border border-white/60 ring-1 ring-black/5 max-h-[90vh] overflow-y-auto">
                    <button type="button" onclick="closeModal('{{ $createRoundModalId }}')"
                        class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                        <span class="material-symbols-rounded text-[24px]">close</span>
                    </button>
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Nové přijímací kolo</h2>
                    <form action="{{ route('admin.application-rounds.store') }}" method="POST" class="space-y-6">
                        @csrf
                        @include('admin.partials.round-form', [
                            'errorsBag' => $roundCreateErrors,
                            'prefix' => 'round-create-' . $program->id,
                            'round' => null,
                            'programs' => $programs,
                            'submitLabel' => 'Vytvořit kolo',
                            'lockedStudyProgram' => $program,
                            'openModalTarget' => $createRoundModalId,
                        ])
                    </form>
                </div>
            </div>
        </div>

        <div id="edit-program-{{ $program->id }}" class="fixed inset-0 z-50 hidden">
            <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm"
                onclick="closeModal('edit-program-{{ $program->id }}')"></div>
            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div
                    class="bg-white/90 backdrop-blur-xl rounded-3xl shadow-2xl w-full max-w-4xl p-8 relative border border-white/60 ring-1 ring-black/5 max-h-[90vh] overflow-y-auto">
                    <button type="button" onclick="closeModal('edit-program-{{ $program->id }}')"
                        class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                        <span class="material-symbols-rounded text-[24px]">close</span>
                    </button>
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Upravit studijní program</h2>
                    <form action="{{ route('admin.programs.update', $program) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PATCH')
                        @include('admin.partials.program-form', [
                            'errorsBag' => $programUpdateErrors,
                            'prefix' => 'program-update-' . $program->id,
                            'program' => $program,
                            'submitLabel' => 'Uložit změny',
                        ])
                    </form>
                </div>
            </div>
        </div>

        @foreach ($program->applicationRounds as $round)
            <div id="edit-round-{{ $round->id }}" class="fixed inset-0 z-50 hidden">
                <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm"
                    onclick="closeModal('edit-round-{{ $round->id }}')"></div>
                <div class="relative min-h-screen flex items-center justify-center p-4">
                    <div
                        class="bg-white/90 backdrop-blur-xl rounded-3xl shadow-2xl w-full max-w-3xl p-8 relative border border-white/60 ring-1 ring-black/5 max-h-[90vh] overflow-y-auto">
                        <button type="button" onclick="closeModal('edit-round-{{ $round->id }}')"
                            class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                            <span class="material-symbols-rounded text-[24px]">close</span>
                        </button>
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Upravit přijímací kolo</h2>
                        <form action="{{ route('admin.application-rounds.update', $round) }}" method="POST"
                            class="space-y-6">
                            @csrf
                            @method('PATCH')
                            @include('admin.partials.round-form', [
                                'errorsBag' => $roundUpdateErrors,
                                'prefix' => 'round-update-' . $round->id,
                                'round' => $round,
                                'programs' => $programs,
                                'submitLabel' => 'Uložit změny',
                            ])
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    @endforeach

    <script>
        function openModal(id) {
            document.getElementById(id)?.classList.remove('hidden');
        }

        function closeModal(id) {
            document.getElementById(id)?.classList.add('hidden');
        }

        @if ($openModal)
            openModal(@json($openModal));
        @endif
    </script>
@endsection
