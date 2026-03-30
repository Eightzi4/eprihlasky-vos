@extends('layouts.admin')

@section('title', 'Přihlášky | Administrace OAUH')

@section('content')
    <div class="bg-white/80 backdrop-blur-xl shadow-sm rounded-3xl overflow-hidden border border-white/60 ring-1 ring-black/5"
        x-data="adminTable({
            applications: {{ Js::from($applications) }},
            programs: {{ Js::from($programs) }}
        })" x-init="$watch('searchTerm', () => currentPage = 1);
        $watch('activeFilters', () => currentPage = 1, { deep: true });
        $watch('selectedProgram', () => currentPage = 1)">

        <div class="px-8 pt-8 pb-6 border-b border-gray-100/80 bg-white/40">
            <h1 class="text-2xl font-bold text-gray-900 mb-0.5">Přihlášky</h1>
            <p class="text-gray-400 text-sm">Správa a kontrola podaných elektronických přihlášek.</p>
        </div>

        <div class="p-6 sm:p-8 space-y-4">

            <div class="flex flex-col sm:grid sm:grid-cols-[auto_1fr] gap-3 sm:items-center">

                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="material-symbols-rounded text-gray-400 text-[18px]">school</span>
                    </div>
                    <select x-model="selectedProgram"
                        class="block pl-9 pr-8 py-2.5 border border-gray-200 rounded-xl bg-white/50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-school-primary focus:border-school-primary text-sm appearance-none shadow-sm text-left w-full md:w-64">
                        <option value="">Všechny obory</option>
                        <template x-for="p in programs" :key="p.id">
                            <option :value="p.id" x-text="p.name"></option>
                        </template>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-2.5 pointer-events-none">
                        <span class="material-symbols-rounded text-gray-400 text-[18px]">expand_more</span>
                    </div>
                </div>

                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="material-symbols-rounded text-gray-400 text-[18px]">search</span>
                    </div>
                    <input type="text" x-model.debounce.300ms="searchTerm" placeholder="Jméno, e-mail, #ID, datum, ..."
                        class="block w-full pl-9 pr-3 py-2.5 border border-gray-200 rounded-xl bg-white/50 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-school-primary focus:border-school-primary text-sm shadow-sm">
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap min-h-[32px]">

                <span class="text-xs font-bold text-gray-400 uppercase tracking-wide flex-shrink-0">Filtry kroků:</span>

                <template x-for="(f, idx) in activeFilters" :key="idx">
                    <div class="flex items-center gap-0.5 bg-gray-50 border border-gray-200 rounded-lg px-1 py-0.5">
                        <div class="relative">
                            <select x-model="f.checkpoint" @change="f.state = ''"
                                class="pl-1.5 pr-5 py-0.5 bg-transparent text-gray-700 font-medium text-xs focus:outline-none appearance-none cursor-pointer">
                                <option value="" disabled>Vyberte krok</option>
                                <option value="identity_verified">Ověření identity</option>
                                <option value="step1">Osobní údaje</option>
                                <option value="gdpr_accepted">Souhlas GDPR</option>
                                <option value="submitted">Přihláška odeslána</option>
                                <option value="step2">Vzdělání</option>
                                <option value="payment">Platba</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-0.5 pointer-events-none">
                                <span class="material-symbols-rounded text-gray-400 text-[13px]">expand_more</span>
                            </div>
                        </div>

                        <span class="text-gray-300 text-xs flex-shrink-0">→</span>

                        <div class="relative">
                            <select x-model="f.state" :disabled="!f.checkpoint"
                                class="pl-1.5 pr-5 py-0.5 bg-transparent text-gray-700 font-medium text-xs focus:outline-none appearance-none cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed">
                                <option value="">Jakýkoliv stav</option>
                                <option value="complete">Splněno</option>
                                <option value="incomplete">Nesplněno</option>
                                <option value="pending">Čeká na schválení</option>
                                <option value="locked">Uzamčeno</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-0.5 pointer-events-none">
                                <span class="material-symbols-rounded text-gray-400 text-[13px]">expand_more</span>
                            </div>
                        </div>

                        <button @click="activeFilters.splice(idx, 1)"
                            class="p-0.5 rounded text-gray-300 hover:text-red-400 hover:bg-red-50 transition-colors cursor-pointer flex-shrink-0">
                            <span class="material-symbols-rounded text-[13px]">close</span>
                        </button>
                    </div>
                </template>

                <button @click="activeFilters.push({ checkpoint: '', state: '' })"
                    class="group flex items-center gap-1 px-2 py-1 rounded-lg border border-dashed border-gray-300 text-gray-400 text-xs font-semibold cursor-pointer flex-shrink-0 transition-colors">
                    <span
                        class="material-symbols-rounded text-[14px] group-hover:text-school-primary group-hover:rotate-90 transition-all duration-300">add</span>
                    <span>Přidat filtr</span>
                </button>

                <template x-if="hasActiveFilters">
                    <button @click="activeFilters = []; selectedProgram = ''; searchTerm = '';"
                        class="ml-auto flex items-center gap-1 font-semibold text-xs text-gray-400 cursor-pointer group flex-shrink-0">
                        <span
                            class="material-symbols-rounded text-[14px] group-hover:text-school-primary transition-colors">close</span>
                        Zrušit vše
                    </button>
                </template>
            </div>

            <div class="overflow-x-auto border border-gray-200/60 rounded-2xl bg-white shadow-sm">
                <table class="w-full min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50/50">
                        <tr>
                            @foreach ([['col' => 'id', 'label' => 'ID'], ['col' => 'last_name', 'label' => 'Uchazeč'], ['col' => 'study_program.name', 'label' => 'Obor'], ['col' => 'round.label', 'label' => 'Kolo'], ['col' => 'dots', 'label' => 'Kroky', 'nosort' => true], ['col' => 'created_at', 'label' => 'Datum']] as $th)
                                <th class="px-5 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider
                                        {{ empty($th['nosort']) ? 'cursor-pointer hover:bg-gray-100 select-none' : '' }} transition-colors"
                                    @click="{{ empty($th['nosort']) ? "sortBy('{$th['col']}')" : '' }}">
                                    <div class="flex items-center gap-1">
                                        {{ $th['label'] }}
                                        @if (empty($th['nosort']))
                                            <span class="material-symbols-rounded text-[16px]"
                                                x-html="sortIcon('{{ $th['col'] }}')"></span>
                                        @endif
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <template x-if="paginatedData.length === 0">
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center text-gray-400">
                                    <span
                                        class="material-symbols-rounded text-[48px] block mb-2 opacity-30">search_off</span>
                                    Nebyly nalezeny žádné přihlášky.
                                </td>
                            </tr>
                        </template>
                        <template x-for="app in paginatedData" :key="app.id">
                            <tr class="hover:bg-red-50/30 transition-colors cursor-pointer group"
                                @click="window.location = `{{ url('/admin/applications') }}/${app.id}`">

                                <td class="px-5 py-4 whitespace-nowrap text-sm font-bold text-gray-500 font-mono"
                                    x-text="app.application_number ? `#${app.application_number}` : `#${app.id}`"></td>

                                <td class="px-5 py-4 text-sm">
                                    <div class="font-bold text-gray-900 group-hover:text-school-primary transition-colors"
                                        x-text="(app.first_name || app.last_name) ? `${app.first_name||''} ${app.last_name||''}`.trim() : 'Nezadáno'">
                                    </div>
                                    <div class="text-xs text-gray-400 mt-0.5" x-text="app.email || app.user?.email || ''">
                                    </div>
                                </td>

                                <td class="px-5 py-4 text-sm text-gray-600 font-medium"
                                    x-text="app.study_program?.name || '—'"></td>

                                <td class="px-5 py-4 text-sm text-gray-500"
                                    x-text="app.round ? (app.round.label || app.round.academic_year) : '—'"></td>

                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-1.5" x-data="{ dots: dotsFor(app) }">
                                        <template x-for="(dot, i) in dots" :key="i">
                                            <span class="h-2.5 w-2.5 rounded-full flex-shrink-0" :class="dot.color"
                                                :title="dot.label"></span>
                                        </template>
                                    </div>
                                </td>

                                <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="flex items-center justify-between gap-4">
                                        <div class="flex flex-col">
                                            <span x-text="new Date(app.created_at).toLocaleDateString('cs-CZ')"></span>
                                            <span x-show="app.submitted_at" class="text-xs text-green-600 font-medium"
                                                x-text="app.submitted_at ? '→ ' + new Date(app.submitted_at).toLocaleDateString('cs-CZ') : ''"></span>
                                        </div>
                                        <span
                                            class="material-symbols-rounded text-gray-300 opacity-0 group-hover:opacity-100 transition-opacity">chevron_right</span>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col sm:flex-row justify-between items-center text-sm text-gray-500 gap-4">
                <p>
                    Zobrazeno <span class="font-bold text-gray-900" x-text="paginatedData.length"></span>
                    z <span class="font-bold text-gray-900" x-text="filteredData.length"></span> záznamů
                </p>
                <div class="flex items-center gap-2">
                    <button @click="currentPage--" :disabled="currentPage <= 1"
                        class="p-2 border border-gray-200 rounded-xl hover:bg-gray-50 disabled:opacity-40 transition-colors cursor-pointer">
                        <span class="material-symbols-rounded text-[18px]">chevron_left</span>
                    </button>
                    <span class="px-2 font-medium tabular-nums">
                        Strana <span x-text="currentPage"></span> z <span x-text="totalPages || 1"></span>
                    </span>
                    <button @click="currentPage++" :disabled="currentPage >= totalPages"
                        class="p-2 border border-gray-200 rounded-xl hover:bg-gray-50 disabled:opacity-40 transition-colors cursor-pointer">
                        <span class="material-symbols-rounded text-[18px]">chevron_right</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('adminTable', (data) => ({
                allData: data.applications,
                programs: data.programs,
                searchTerm: '',
                activeFilters: [],
                selectedProgram: '',
                sortColumn: 'created_at',
                sortDirection: 'desc',
                currentPage: 1,
                itemsPerPage: 15,

                get hasActiveFilters() {
                    return this.activeFilters.some(f => f.checkpoint) ||
                        !!this.selectedProgram;
                },

                checkpointLabel(k) {
                    return {
                        identity_verified: 'Ověření identity',
                        step1: 'Osobní údaje',
                        gdpr_accepted: 'Souhlas GDPR',
                        submitted: 'Přihláška odeslána',
                        step2: 'Vzdělání',
                        payment: 'Platba'
                    } [k] || k;
                },
                stateLabel(s) {
                    return {
                        complete: 'Splněno',
                        incomplete: 'Nesplněno',
                        pending: 'Čeká na schválení',
                        locked: 'Uzamčeno'
                    } [s] || s;
                },

                checkpointStatus(app, key) {
                    const locked = !!app.submitted;
                    switch (key) {
                        case 'identity_verified':
                            return locked ? 'locked' : (app.identity_verified ? 'complete' :
                                'incomplete');
                        case 'step1': {
                            const req = ['first_name', 'last_name', 'gender', 'birth_number',
                                'birth_date',
                                'birth_city', 'citizenship', 'email', 'phone', 'street', 'city',
                                'zip', 'country'
                            ];
                            const done = req.every(f => app[f]) && app.identity_verified;
                            return locked ? 'locked' : (done ? 'complete' : 'incomplete');
                        }
                        case 'gdpr_accepted':
                            return locked ? 'locked' : (app.gdpr_accepted ? 'complete' : 'incomplete');
                        case 'submitted':
                            return app.submitted ? 'complete' : 'incomplete';
                        case 'step2': {
                            if (app.prev_study_info_accepted) return 'complete';
                            if (app.prev_study_info) return 'pending';
                            return 'incomplete';
                        }
                        case 'payment':
                            if (app.payment_accepted) return 'complete';
                            if (app.paid) return 'pending';
                            return 'incomplete';
                        default:
                            return 'incomplete';
                    }
                },

                dotColor(status) {
                    return {
                        complete: 'bg-green-500',
                        incomplete: 'bg-orange-400',
                        pending: 'bg-blue-400',
                        locked: 'bg-gray-300'
                    } [status] || 'bg-gray-200';
                },

                dotsFor(app) {
                    return [{
                            key: 'identity_verified',
                            label: 'Ověření Identity'
                        },
                        {
                            key: 'step1',
                            label: 'Osobní údaje'
                        },
                        {
                            key: 'gdpr_accepted',
                            label: 'Souhlas GDPR'
                        },
                        {
                            key: 'submitted',
                            label: 'Přihláška odeslána'
                        },
                        {
                            key: 'step2',
                            label: 'Vzdělání'
                        },
                        {
                            key: 'payment',
                            label: 'Platba'
                        },
                    ].map(c => ({
                        label: c.label,
                        color: this.dotColor(this.checkpointStatus(app, c.key))
                    }));
                },

                sortBy(col) {
                    this.sortDirection = this.sortColumn === col ?
                        (this.sortDirection === 'asc' ? 'desc' : 'asc') : 'asc';
                    this.sortColumn = col;
                    this.currentPage = 1;
                },
                sortIcon(col) {
                    if (this.sortColumn !== col) return 'unfold_more';
                    return this.sortDirection === 'asc' ? 'keyboard_arrow_up' : 'keyboard_arrow_down';
                },

                formatCzech(dateStr) {
                    if (!dateStr) return '';
                    const d = new Date(dateStr);
                    if (isNaN(d)) return '';
                    return new Date(Date.UTC(d.getUTCFullYear(), d.getUTCMonth(), d.getUTCDate()))
                        .toLocaleDateString('cs-CZ', {
                            day: 'numeric',
                            month: 'numeric',
                            year: 'numeric',
                            timeZone: 'UTC'
                        });
                },

                dateMatchesTerm(app, t) {
                    const created = this.formatCzech(app.created_at);
                    const submitted = this.formatCzech(app.submitted_at);
                    return created.includes(t) || submitted.includes(t);
                },

                get filteredData() {
                    const getProp = (obj, path) => path.split('.').reduce((o, k) => o?.[k], obj) ??
                        '';

                    let rows = [...this.allData];

                    const rules = this.activeFilters.filter(f => f.checkpoint);
                    if (rules.length) {
                        rows = rows.filter(app =>
                            rules.every(f => {
                                const s = this.checkpointStatus(app, f.checkpoint);
                                return f.state ? s === f.state : true;
                            })
                        );
                    }

                    if (this.selectedProgram) {
                        rows = rows.filter(a => String(a.study_program_id) === String(this
                            .selectedProgram));
                    }

                    const raw = this.searchTerm.trim();
                    if (raw) {
                        const t = raw.toLowerCase();

                        if (raw.startsWith('#')) {
                            const idTerm = raw.slice(1);
                            rows = rows.filter(a =>
                                String(a.id).includes(idTerm) ||
                                (a.application_number && String(a.application_number).includes(
                                    idTerm))
                            );
                        } else {
                            rows = rows.filter(a =>
                                this.dateMatchesTerm(a, t) || [
                                    a.first_name, a.last_name,
                                    a.email, a.user?.email,
                                    String(a.id), a.application_number,
                                    a.round?.label, a.round?.academic_year,
                                    a.study_program?.name,
                                    a.previous_school, a.previous_study_field,
                                    a.birth_city, a.city, a.phone,
                                    a.birth_number, a.citizenship,
                                ].some(v => v && String(v).toLowerCase().includes(t))
                            );
                        }
                    }

                    return rows.sort((a, b) => {
                        const av = getProp(a, this.sortColumn);
                        const bv = getProp(b, this.sortColumn);
                        const cmp = typeof av === 'string' && typeof bv === 'string' ?
                            av.localeCompare(bv, 'cs') :
                            (av < bv ? -1 : av > bv ? 1 : 0);
                        return this.sortDirection === 'asc' ? cmp : -cmp;
                    });
                },

                get totalPages() {
                    return Math.ceil(this.filteredData.length / this.itemsPerPage);
                },
                get paginatedData() {
                    const s = (this.currentPage - 1) * this.itemsPerPage;
                    return this.filteredData.slice(s, s + this.itemsPerPage);
                },
            }));
        });
    </script>
@endsection
