@extends('layouts.admin')

@section('title', 'Administrátoři | Administrace OAUH')

@section('content')
    @php
        $adminCreateErrors = $errors->adminCreate;
        $adminUpdateErrors = $errors->adminUpdate;
        $openModal = session('open_modal');
        $currentAdminId = Auth::guard('admin')->id();
    @endphp

    <div class="space-y-8"
        x-data="{
            admins: {{ Js::from($admins->map(fn($admin) => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'is_main_admin' => $admin->is_main_admin,
                'is_self' => $admin->id === $currentAdminId,
                'edit_modal' => 'edit-admin-' . $admin->id,
                'delete_url' => route('admin.admins.destroy', $admin),
            ])) }},
            searchTerm: '',
            sortColumn: 'id',
            sortDirection: 'asc',
            get filteredAdmins() {
                const term = this.searchTerm.trim().toLowerCase();
                let rows = this.admins.filter(admin =>
                    !term ||
                    admin.name.toLowerCase().includes(term) ||
                    admin.email.toLowerCase().includes(term)
                );
                const direction = this.sortDirection === 'asc' ? 1 : -1;
                rows.sort((a, b) => {
                    const av = this.valueForSort(a, this.sortColumn);
                    const bv = this.valueForSort(b, this.sortColumn);
                    if (typeof av === 'string' && typeof bv === 'string') {
                        return av.localeCompare(bv, 'cs') * direction;
                    }
                    return ((av < bv) ? -1 : (av > bv ? 1 : 0)) * direction;
                });
                return rows;
            },
            valueForSort(admin, column) {
                if (column === 'role') return admin.is_main_admin ? 0 : 1;
                return admin[column];
            },
            sortBy(column) {
                this.sortDirection = this.sortColumn === column
                    ? (this.sortDirection === 'asc' ? 'desc' : 'asc')
                    : 'asc';
                this.sortColumn = column;
            },
            sortIcon(column) {
                if (this.sortColumn !== column) return 'unfold_more';
                return this.sortDirection === 'asc' ? 'keyboard_arrow_up' : 'keyboard_arrow_down';
            }
        }">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach ([['label' => 'Všechny účty', 'value' => $admins->count(), 'icon' => 'manage_accounts'], ['label' => 'Hlavní administrátoři', 'value' => $admins->where('is_main_admin', true)->count(), 'icon' => 'shield_person'], ['label' => 'Běžní administrátoři', 'value' => $admins->where('is_main_admin', false)->count(), 'icon' => 'badge']] as $stat)
                <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-white/60 ring-1 ring-black/5 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">{{ $stat['label'] }}</p>
                        <span class="material-symbols-rounded text-gray-500 text-[22px]">{{ $stat['icon'] }}</span>
                    </div>
                    <p class="text-3xl font-bold text-gray-900">{{ $stat['value'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-sm border border-white/60 ring-1 ring-black/5 overflow-hidden">
            <div class="px-8 py-5 border-b border-gray-100/80 bg-white/40 flex flex-col sm:grid sm:grid-cols-[1fr_auto] gap-4 sm:items-center">
                <div>
                    <h2 class="font-bold text-gray-800 text-lg">Administrátorské účty</h2>
                    <p class="text-sm text-gray-400">Vyhledávání podle jména i e-mailu a rychlé třídění podle důležitých sloupců.</p>
                </div>

                <button type="button" onclick="openModal('create-admin')"
                    class="group relative flex items-center justify-center px-5 py-2.5 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 cursor-pointer">
                    <div class="absolute inset-0 topo-bg opacity-40"></div>
                    <div class="absolute inset-0 bg-white/60 backdrop-blur-[2px] group-hover:backdrop-blur-[4px] transition-all duration-300"></div>
                    <div class="absolute inset-0 rounded-xl border border-white/60 border-b-2 border-b-gray-200/50"></div>
                    <span class="relative z-10 text-gray-900 font-bold text-sm flex items-center whitespace-nowrap">
                        <span class="material-symbols-rounded mr-2 text-[18px] text-gray-500 group-hover:text-school-primary group-hover:rotate-90 transition-all duration-300">add</span>
                        Přidat administrátora
                    </span>
                </button>
            </div>

            <div class="p-6 sm:p-8 space-y-4">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="material-symbols-rounded text-gray-400 text-[18px]">search</span>
                    </div>
                    <input type="text" x-model.debounce.200ms="searchTerm" placeholder="Hledat podle jména nebo e-mailu"
                        class="block w-full pl-9 pr-3 py-2.5 border border-gray-200 rounded-xl bg-white/50 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-school-primary focus:border-school-primary text-sm shadow-sm">
                </div>

                <div class="overflow-x-auto border border-gray-200/60 rounded-2xl bg-white shadow-sm">
                    <table class="w-full min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50/50">
                            <tr>
                                @foreach ([['id' => 'id', 'label' => 'ID'], ['id' => 'name', 'label' => 'Jméno'], ['id' => 'email', 'label' => 'E-mail'], ['id' => 'role', 'label' => 'Role']] as $th)
                                    <th class="px-5 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 select-none transition-colors"
                                        @click="sortBy('{{ $th['id'] }}')">
                                        <div class="flex items-center gap-1">
                                            {{ $th['label'] }}
                                            <span class="material-symbols-rounded text-[16px]" x-text="sortIcon('{{ $th['id'] }}')"></span>
                                        </div>
                                    </th>
                                @endforeach
                                <th class="px-5 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Akce</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <template x-if="filteredAdmins.length === 0">
                                <tr>
                                    <td colspan="5" class="px-6 py-16 text-center text-gray-400">
                                        <span class="material-symbols-rounded text-[48px] block mb-2 opacity-30">search_off</span>
                                        Nebyl nalezen žádný administrátorský účet.
                                    </td>
                                </tr>
                            </template>

                            <template x-for="admin in filteredAdmins" :key="admin.id">
                                <tr :class="admin.is_self ? 'bg-amber-50/70' : 'hover:bg-red-50/30'" class="transition-colors">
                                    <td class="px-5 py-4 whitespace-nowrap text-sm font-bold text-gray-500 font-mono" x-text="`#${admin.id}`"></td>
                                    <td class="px-5 py-4 text-sm font-bold text-gray-900" x-text="admin.name"></td>
                                    <td class="px-5 py-4 text-sm text-gray-500" x-text="admin.email"></td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border"
                                            :class="admin.is_main_admin ? 'bg-red-50 text-school-primary border-red-200' : 'bg-gray-100 text-gray-500 border-gray-200'">
                                            <span class="material-symbols-rounded text-[14px]" x-text="admin.is_main_admin ? 'shield_person' : 'badge'"></span>
                                            <span x-text="admin.is_main_admin ? 'Hlavní administrátor' : 'Administrátor'"></span>
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <div x-show="!admin.is_self" class="flex items-center gap-2">
                                            <button type="button" @click="openModal(admin.edit_modal)"
                                                class="group relative flex items-center justify-center w-10 h-10 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 cursor-pointer">
                                                <div class="absolute inset-0 topo-bg opacity-30"></div>
                                                <div class="absolute inset-0 bg-white/60 backdrop-blur-[2px] group-hover:backdrop-blur-[4px] transition-all duration-300"></div>
                                                <div class="absolute inset-0 rounded-xl border border-white/60 border-b-2 border-b-gray-200/50"></div>
                                                <span class="relative z-10 material-symbols-rounded text-[18px] text-gray-500 group-hover:text-school-primary transition-colors">edit</span>
                                            </button>

                                            <form method="POST" :action="admin.delete_url" onsubmit="return confirm('Opravdu chcete odstranit tento administrátorský účet?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="group relative flex items-center justify-center w-10 h-10 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 cursor-pointer">
                                                    <div class="absolute inset-0 topo-bg opacity-30"></div>
                                                    <div class="absolute inset-0 bg-white/60 backdrop-blur-[2px] group-hover:backdrop-blur-[4px] transition-all duration-300"></div>
                                                    <div class="absolute inset-0 rounded-xl border border-white/60 border-b-2 border-b-gray-200/50"></div>
                                                    <span class="relative z-10 material-symbols-rounded text-[18px] text-gray-500 group-hover:text-school-primary transition-colors">delete</span>
                                                </button>
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
    </div>

    <div id="create-admin" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm" onclick="closeModal('create-admin')"></div>
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="bg-white/90 backdrop-blur-xl rounded-3xl shadow-2xl w-full max-w-2xl p-8 relative border border-white/60 ring-1 ring-black/5 max-h-[90vh] overflow-y-auto">
                <button type="button" onclick="closeModal('create-admin')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                    <span class="material-symbols-rounded text-[24px]">close</span>
                </button>
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Nový administrátorský účet</h2>
                <form action="{{ route('admin.admins.store') }}" method="POST" class="space-y-6">
                    @csrf
                    @include('admin.partials.admin-form', ['errorsBag' => $adminCreateErrors, 'prefix' => 'admin-create', 'adminModel' => null, 'submitLabel' => 'Vytvořit účet'])
                </form>
            </div>
        </div>
    </div>

    @foreach ($admins as $managedAdmin)
        @if ($managedAdmin->id !== $currentAdminId)
            <div id="edit-admin-{{ $managedAdmin->id }}" class="fixed inset-0 z-50 hidden">
                <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm" onclick="closeModal('edit-admin-{{ $managedAdmin->id }}')"></div>
                <div class="relative min-h-screen flex items-center justify-center p-4">
                    <div class="bg-white/90 backdrop-blur-xl rounded-3xl shadow-2xl w-full max-w-xl p-8 relative border border-white/60 ring-1 ring-black/5 max-h-[90vh] overflow-y-auto">
                        <button type="button" onclick="closeModal('edit-admin-{{ $managedAdmin->id }}')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                            <span class="material-symbols-rounded text-[24px]">close</span>
                        </button>
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Upravit účet administrátora</h2>
                        <form action="{{ route('admin.admins.update', $managedAdmin) }}" method="POST" class="space-y-6">
                            @csrf
                            @method('PATCH')
                            @include('admin.partials.admin-edit-form', ['errorsBag' => $adminUpdateErrors, 'prefix' => 'admin-update-' . $managedAdmin->id, 'adminModel' => $managedAdmin, 'submitLabel' => 'Uložit změny'])
                        </form>
                    </div>
                </div>
            </div>
        @endif
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
