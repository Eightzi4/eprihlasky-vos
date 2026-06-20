@extends('layouts.admin')

@section('title', 'Přihlášky | Administrace OAUH')

@section('content')
    <div class="bg-white/80 backdrop-blur-xl shadow-sm rounded-3xl overflow-hidden border border-white/60 ring-1 ring-black/5"
        x-data="adminTable({
            applications: {{ Js::from($applicationsData) }},
            programs: {{ Js::from($programs) }},
            rounds: {{ Js::from($rounds) }},
            bulkExportUrls: {
                csv: '{{ route('admin.applications.bulk.export.csv') }}',
                pdf: '{{ route('admin.applications.bulk.export.pdf') }}',
                zip: '{{ route('admin.applications.bulk.export.zip') }}',
            }
        })" x-init="$watch('searchTerm', () => currentPage = 1);
        $watch('activeFilters', () => currentPage = 1, { deep: true });
        $watch('selectedProgram', () => { currentPage = 1; selectedRound = ''; });
        $watch('selectedRound', () => currentPage = 1)">

        <div class="px-8 pt-8 pb-6 border-b border-gray-100/80 bg-white/40">
            <h1 class="text-2xl font-bold text-gray-900 mb-0.5">Přihlášky</h1>
            <p class="text-gray-400 text-sm">Správa a kontrola podaných elektronických přihlášek.</p>
        </div>

        <div class="p-6 sm:p-8 space-y-4">

            <div class="flex items-center gap-3 flex-wrap bg-white/80 border border-gray-200 rounded-2xl px-4 py-2.5">

                <span class="text-xs font-bold text-gray-500 uppercase tracking-wide flex-shrink-0">Obory a kola:</span>

                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="material-symbols-rounded text-gray-400 text-[18px]">school</span>
                    </div>
                    <select x-model="selectedProgram"
                        class="block pl-9 pr-8 py-2 border border-gray-200 rounded-xl bg-white/50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-school-primary focus:border-school-primary text-sm appearance-none text-left w-48">
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
                        <span class="material-symbols-rounded text-gray-400 text-[18px]">event</span>
                    </div>
                    <select x-model="selectedRound"
                        class="block pl-9 pr-8 py-2 border border-gray-200 rounded-xl bg-white/50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-school-primary focus:border-school-primary text-sm appearance-none text-left w-56">
                        <option value="">Všechna kola</option>
                        <template x-for="r in rounds" :key="r.id">
                            <option x-show="!selectedProgram || r.study_program_id == selectedProgram" :value="r.id"
                                x-text="(r.label || r.academic_year) + (r.study_program ? ' \u2014 ' + r.study_program.name : '')"></option>
                        </template>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-2.5 pointer-events-none">
                        <span class="material-symbols-rounded text-gray-400 text-[18px]">expand_more</span>
                    </div>
                </div>

                <div class="h-5 w-px bg-gray-200"></div>

                <div class="relative flex-1 min-w-[180px]">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="material-symbols-rounded text-gray-400 text-[18px]">search</span>
                    </div>
                    <input type="text" x-model.debounce.300ms="searchTerm" placeholder="Jméno, e-mail, #ID, datum, ..."
                        class="block w-full pl-9 pr-3 py-2 border border-gray-200 rounded-xl bg-white/50 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-school-primary focus:border-school-primary text-sm">
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap min-h-[32px] bg-white/80 border border-gray-200 rounded-2xl px-4 py-2.5">

                <span class="text-xs font-bold text-gray-500 uppercase tracking-wide flex-shrink-0">Filtry kroků:</span>

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

                        <span class="text-gray-300 text-xs flex-shrink-0">&rarr;</span>

                        <div class="relative">
                            <select x-model="f.state" :disabled="!f.checkpoint"
                                class="pl-1.5 pr-5 py-0.5 bg-transparent text-gray-700 font-medium text-xs focus:outline-none appearance-none cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed">
                                <option value="">Jakýkoliv stav</option>
                                <option value="complete">Splněno</option>
                                <option value="incomplete">Nesplněno</option>
                                <option value="pending">Čeká na schválení</option>
                                <option value="failed">Nesplněno po termínu</option>
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
                    <span class="material-symbols-rounded text-[14px] group-hover:text-school-primary group-hover:rotate-90 transition-all duration-300">add</span>
                    <span>Přidat filtr</span>
                </button>

                <template x-if="hasActiveFilters">
                    <button @click="activeFilters = []; selectedProgram = ''; selectedRound = ''; searchTerm = '';"
                        class="ml-auto flex items-center gap-1 font-semibold text-xs text-gray-500 hover:text-gray-700 cursor-pointer flex-shrink-0">
                        <span class="material-symbols-rounded text-[14px]">close</span>
                        Zrušit vše
                    </button>
                </template>
            </div>

            <template x-if="selectedIds.length">
                <div class="flex items-center gap-3 flex-wrap bg-white/80 border border-gray-200 rounded-2xl px-4 py-2.5">
                    <span class="text-sm font-bold text-gray-700">
                        Vybráno <span x-text="selectedIds.length" class="text-school-primary"></span>
                    </span>

                    <button @click="selectedIds = []"
                        class="text-xs font-semibold text-gray-500 hover:text-gray-700 cursor-pointer">Zrušit výběr</button>
                    <button @click="selectedIds = filteredData.map(a => a.id)"
                        class="text-xs font-semibold text-gray-500 hover:text-gray-700 cursor-pointer">Vybrat vše</button>
                    <button @click="invertSelection()"
                        class="text-xs font-semibold text-gray-500 hover:text-gray-700 cursor-pointer">Invertovat</button>

                    <div class="h-5 w-px bg-gray-200"></div>

                    <form method="POST" action="{{ route('admin.applications.bulk.export.csv') }}" class="inline">
                        @csrf
                        <template x-for="id in selectedIds" :key="id">
                            <input type="hidden" name="ids[]" :value="id">
                        </template>
                        <x-button as="button" type="submit" text="CSV" icon="table_view"
                            variant="secondary" size="xs" spanClass="text-gray-700" />
                    </form>

                    <form method="POST" action="{{ route('admin.applications.bulk.export.pdf') }}" class="inline">
                        @csrf
                        <template x-for="id in selectedIds" :key="id">
                            <input type="hidden" name="ids[]" :value="id">
                        </template>
                        <x-button as="button" type="submit" text="PDF" icon="picture_as_pdf"
                            variant="secondary" size="xs" spanClass="text-gray-700" />
                    </form>

                    <x-button as="button" onclick="openModal('bulk-zip-modal')"
                        text="ZIP" icon="folder_zip"
                        variant="secondary" size="xs" spanClass="text-gray-700" />
                </div>
            </template>

            <div class="overflow-x-auto border border-gray-200/60 rounded-2xl bg-white shadow-sm">
                <table class="w-full min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-3 py-4 w-10">
                                <label class="relative flex items-center justify-center cursor-pointer">
                                    <input type="checkbox" x-model="allSelected"
                                        class="peer h-4 w-4 cursor-pointer appearance-none rounded border-2 border-gray-300 bg-white transition-all checked:border-school-primary checked:bg-school-primary hover:border-school-primary">
                                    <span class="absolute text-white opacity-0 peer-checked:opacity-100 pointer-events-none transition-opacity">
                                        <span class="material-symbols-rounded text-[14px] font-bold">check</span>
                                    </span>
                                </label>
                            </th>
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
                                <td colspan="7" class="px-6 py-16 text-center text-gray-400">
                                    <span class="material-symbols-rounded text-[48px] block mb-2 opacity-30">search_off</span>
                                    Nebyly nalezeny žádné přihlášky.
                                </td>
                            </tr>
                        </template>
                        <template x-for="app in paginatedData" :key="app.id">
                            <tr class="hover:bg-red-50/30 transition-colors group"
                                :class="{ 'bg-red-50/50': selectedIds.includes(app.id) }">

                                <td class="px-3 py-4 w-10" @click.stop="">
                                    <label class="relative flex items-center justify-center cursor-pointer">
                                        <input type="checkbox" :value="app.id" x-model="selectedIds"
                                            class="peer h-4 w-4 cursor-pointer appearance-none rounded border-2 border-gray-300 bg-white transition-all checked:border-school-primary checked:bg-school-primary hover:border-school-primary">
                                        <span class="absolute text-white opacity-0 peer-checked:opacity-100 pointer-events-none transition-opacity">
                                            <span class="material-symbols-rounded text-[14px] font-bold">check</span>
                                        </span>
                                    </label>
                                </td>

                                <td class="px-5 py-4 whitespace-nowrap text-sm font-bold text-gray-500 font-mono cursor-pointer"
                                    @click="navigateTo(`{{ url('/admin/applications') }}/${app.id}`)"
                                    x-text="app.application_number ? `#${app.application_number}` : `#${app.id}`"></td>

                                <td class="px-5 py-4 text-sm cursor-pointer"
                                    @click="navigateTo(`{{ url('/admin/applications') }}/${app.id}`)">
                                    <div class="font-bold text-gray-900 group-hover:text-school-primary transition-colors"
                                        x-text="(app.first_name || app.last_name) ? `${app.first_name||''} ${app.last_name||''}`.trim() : 'Nezadáno'">
                                    </div>
                                    <div class="text-xs text-gray-400 mt-0.5" x-text="app.email || app.user?.email || ''">
                                    </div>
                                </td>

                                <td class="px-5 py-4 text-sm text-gray-600 font-medium cursor-pointer"
                                    @click="navigateTo(`{{ url('/admin/applications') }}/${app.id}`)"
                                    x-text="app.study_program?.name || '—'"></td>

                                <td class="px-5 py-4 text-sm text-gray-500 cursor-pointer"
                                    @click="navigateTo(`{{ url('/admin/applications') }}/${app.id}`)"
                                    x-text="app.round ? (app.round.label || app.round.academic_year) : '—'"></td>

                                <td class="px-5 py-4 cursor-pointer"
                                    @click="navigateTo(`{{ url('/admin/applications') }}/${app.id}`)">
                                    <div class="flex items-center gap-1.5" x-data="{ dots: dotsFor(app) }">
                                        <template x-for="(dot, i) in dots" :key="i">
                                            <span class="h-2.5 w-2.5 rounded-full flex-shrink-0" :class="dot.color"
                                                :title="dot.label"></span>
                                        </template>
                                    </div>
                                </td>

                                <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-500 cursor-pointer"
                                    @click="navigateTo(`{{ url('/admin/applications') }}/${app.id}`)">
                                    <div class="flex items-center justify-between gap-4">
                                        <div class="flex flex-col">
                                            <span x-text="new Date(app.created_at).toLocaleDateString('cs-CZ')"></span>
                                            <span x-show="app.submitted_at" class="text-xs text-green-600 font-medium"
                                                x-text="app.submitted_at ? '→ ' + new Date(app.submitted_at).toLocaleDateString('cs-CZ') : ''"></span>
                                        </div>
                                        <span class="material-symbols-rounded text-gray-300 opacity-0 group-hover:opacity-100 transition-opacity">chevron_right</span>
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

    <div id="bulk-zip-modal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm" onclick="closeModal('bulk-zip-modal')"></div>
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="bg-white/90 backdrop-blur-xl rounded-3xl shadow-2xl w-full max-w-md p-8 relative border border-white/60 ring-1 ring-black/5">
                <button type="button" onclick="closeModal('bulk-zip-modal')"
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                    <span class="material-symbols-rounded text-[24px]">close</span>
                </button>
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Exportovat ZIP</h2>
                <form method="POST" action="{{ route('admin.applications.bulk.export.zip') }}" x-data="{ zipCsv: true, zipPdf: true, zipEdu: true, zipPay: true, zipOther: true }" class="space-y-5">
                    @csrf
                    <template x-for="id in selectedIds" :key="id">
                        <input type="hidden" name="ids[]" :value="id">
                    </template>
                    <p class="text-sm text-gray-500">Vyberte, co chcete zahrnout do ZIP archivu:</p>

                    @foreach ([['name' => 'csv', 'label' => 'CSV soubor', 'icon' => 'table_view', 'model' => 'zipCsv'], ['name' => 'pdf', 'label' => 'PDF soubor', 'icon' => 'picture_as_pdf', 'model' => 'zipPdf'], ['name' => 'education', 'label' => 'Doklady o vzdělání', 'icon' => 'school', 'model' => 'zipEdu'], ['name' => 'payment', 'label' => 'Potvrzení o platbě', 'icon' => 'receipt_long', 'model' => 'zipPay'], ['name' => 'other', 'label' => 'Přílohy', 'icon' => 'attach_file', 'model' => 'zipOther']] as $opt)
                        <label class="flex items-center gap-4 cursor-pointer group">
                            <div class="relative flex items-center flex-shrink-0">
                                <input type="checkbox" name="{{ $opt['name'] }}" value="1" checked x-model="{{ $opt['model'] }}"
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

    <script>
        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }
        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }

        document.addEventListener('alpine:init', () => {
            Alpine.data('adminTable', (data) => ({
                allData: data.applications,
                programs: data.programs,
                rounds: data.rounds,
                bulkExportUrls: data.bulkExportUrls,
                searchTerm: '',
                activeFilters: [],
                selectedProgram: '',
                selectedRound: '',
                sortColumn: 'created_at',
                sortDirection: 'desc',
                currentPage: 1,
                itemsPerPage: 15,
                selectedIds: [],

                init() {
                    this.restoreState();
                    window.addEventListener('beforeunload', () => this.saveState());
                },

                navigateTo(url) {
                    this.saveState();
                    window.location = url;
                },

                saveState() {
                    const state = {
                        searchTerm: this.searchTerm,
                        activeFilters: JSON.parse(JSON.stringify(this.activeFilters)),
                        selectedProgram: this.selectedProgram,
                        selectedRound: this.selectedRound,
                        sortColumn: this.sortColumn,
                        sortDirection: this.sortDirection,
                        currentPage: this.currentPage,
                        selectedIds: JSON.parse(JSON.stringify(this.selectedIds)),
                    };
                    sessionStorage.setItem('adminAppsState', JSON.stringify(state));
                },

                restoreState() {
                    try {
                        const raw = sessionStorage.getItem('adminAppsState');
                        if (!raw) return;
                        const state = JSON.parse(raw);
                        if (state.searchTerm !== undefined) this.searchTerm = state.searchTerm;
                        if (state.activeFilters) this.activeFilters = state.activeFilters;
                        if (state.selectedProgram !== undefined) this.selectedProgram = state.selectedProgram;
                        if (state.selectedRound !== undefined) this.selectedRound = state.selectedRound;
                        if (state.sortColumn) this.sortColumn = state.sortColumn;
                        if (state.sortDirection) this.sortDirection = state.sortDirection;
                        if (state.currentPage) this.currentPage = state.currentPage;
                        if (state.selectedIds) this.selectedIds = state.selectedIds;
                    } catch (e) {}
                },

                get hasActiveFilters() {
                    return this.activeFilters.some(f => f.checkpoint) ||
                        !!this.selectedProgram || !!this.selectedRound;
                },

                get allSelected() {
                    return this.filteredData.length > 0 &&
                        this.filteredData.every(a => this.selectedIds.includes(a.id));
                },
                set allSelected(val) {
                    if (val) {
                        this.selectedIds = this.filteredData.map(a => a.id);
                    } else {
                        this.selectedIds = [];
                    }
                },

                invertSelection() {
                    const allIds = this.filteredData.map(a => a.id);
                    const inSet = new Set(this.selectedIds);
                    this.selectedIds = allIds.filter(id => !inSet.has(id));
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
                        failed: 'Nesplněno po termínu',
                        locked: 'Uzamčeno'
                    } [s] || s;
                },

                checkpointStatus(app, key) {
                    return app.checkpoint_statuses?.[key] || 'incomplete';
                },

                dotColor(status) {
                    return {
                        complete: 'bg-green-500',
                        incomplete: 'bg-orange-400',
                        pending: 'bg-blue-400',
                        failed: 'bg-school-primary',
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
                        label: `${c.label}: ${this.stateLabel(this.checkpointStatus(app, c.key))}`,
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
                    const getProp = (obj, path) => path.split('.').reduce((o, k) => o?.[k], obj) ?? '';

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
                        rows = rows.filter(a => String(a.study_program_id) === String(this.selectedProgram));
                    }

                    if (this.selectedRound) {
                        rows = rows.filter(a => String(a.round_id) === String(this.selectedRound));
                    }

                    const raw = this.searchTerm.trim();
                    if (raw) {
                        const t = raw.toLowerCase();

                        if (raw.startsWith('#')) {
                            const idTerm = raw.slice(1);
                            rows = rows.filter(a =>
                                String(a.id).includes(idTerm) ||
                                (a.application_number && String(a.application_number).includes(idTerm))
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
