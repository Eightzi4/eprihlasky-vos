@extends('layouts.admin')

@section('title', 'Přehled | Administrace OAUH')

@section('content')
    @php $admin = Auth::guard('admin')->user(); @endphp

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        @foreach ([['label' => 'Celkem přihlášek', 'value' => $stats['total'], 'icon' => 'description', 'cls' => 'text-gray-500'], ['label' => 'Odesláno', 'value' => $stats['submitted'], 'icon' => 'check_circle', 'cls' => 'text-green-500'], ['label' => 'Rozpracováno', 'value' => $stats['drafts'], 'icon' => 'edit_note', 'cls' => 'text-amber-500'], ['label' => 'Čeká na platbu', 'value' => $stats['awaiting_payment'], 'icon' => 'payments', 'cls' => 'text-blue-500']] as $stat)
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-white/60 ring-1 ring-black/5 p-5">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">{{ $stat['label'] }}</p>
                    <span class="material-symbols-rounded {{ $stat['cls'] }} text-[22px]">{{ $stat['icon'] }}</span>
                </div>
                <p class="text-3xl font-bold text-gray-900">{{ $stat['value'] }}</p>
            </div>
        @endforeach
    </div>

    <div
        class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-sm border border-white/60 ring-1 ring-black/5 overflow-hidden">
        <div class="px-8 py-5 border-b border-gray-100/80 bg-white/40 flex items-center gap-3">
            <div class="h-8 w-8 rounded-lg bg-red-50 flex items-center justify-center flex-shrink-0">
                <span class="material-symbols-rounded text-school-primary text-[20px]">manage_accounts</span>
            </div>
            <h2 class="font-bold text-gray-800 text-lg">Nastavení účtu</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2">
            <div class="p-8 border-b md:border-b-0 md:border-r border-gray-200/60 flex flex-col justify-between gap-5">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Kontaktní e-mail</p>
                    <p class="text-lg font-bold text-gray-900 break-all mb-1">{{ $admin->email }}</p>
                    <p class="text-sm text-gray-500">E-mailová adresa, pomocí které se přihlašujete.</p>
                </div>
                <button onclick="openModal('email-modal')"
                    class="group relative flex items-center justify-center px-5 py-2.5 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 cursor-pointer self-start">
                    <div class="absolute inset-0 topo-bg opacity-40"></div>
                    <div
                        class="absolute inset-0 bg-white/60 backdrop-blur-[2px] group-hover:backdrop-blur-[4px] transition-all duration-300">
                    </div>
                    <div class="absolute inset-0 rounded-xl border border-white/60 border-b-2 border-b-gray-200/50"></div>
                    <span class="relative z-10 text-gray-700 font-bold text-sm flex items-center">
                        <span
                            class="material-symbols-rounded mr-2 text-[18px] text-gray-500 group-hover:text-school-primary transition-colors">edit</span>
                        Změnit e-mail
                    </span>
                </button>
            </div>

            <div class="p-8 flex flex-col justify-between gap-5">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Heslo k účtu</p>
                    @if ($admin->password)
                        <div class="flex items-center gap-2 text-green-600 font-bold text-lg">
                            <span class="material-symbols-rounded text-[20px]">check_circle</span>
                            Heslo je nastaveno
                        </div>
                    @else
                        <div class="flex items-center gap-2 text-orange-500 font-bold text-lg">
                            <span class="material-symbols-rounded text-[20px]">warning</span>
                            Heslo není nastaveno
                        </div>
                        <p class="text-sm text-gray-500 mt-1">Bez hesla se přihlašujete pouze odkazem z e-mailu.</p>
                    @endif
                </div>
                <button onclick="openModal('password-modal')"
                    class="group relative flex items-center justify-center px-5 py-2.5 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 cursor-pointer self-start">
                    <div class="absolute inset-0 topo-bg opacity-40"></div>
                    <div
                        class="absolute inset-0 bg-white/60 backdrop-blur-[2px] group-hover:backdrop-blur-[4px] transition-all duration-300">
                    </div>
                    <div class="absolute inset-0 rounded-xl border border-white/60 border-b-2 border-b-gray-200/50"></div>
                    <span class="relative z-10 text-gray-700 font-bold text-sm flex items-center">
                        <span
                            class="material-symbols-rounded mr-2 text-[18px] text-gray-500 group-hover:text-school-primary transition-colors">lock_reset</span>
                        {{ $admin->password ? 'Změnit heslo' : 'Vytvořit heslo' }}
                    </span>
                </button>
            </div>
        </div>
    </div>

    <div id="email-modal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm" onclick="closeModal('email-modal')"></div>
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div
                class="bg-white/90 backdrop-blur-xl rounded-3xl shadow-2xl w-full max-w-md p-8 relative border border-white/60 ring-1 ring-black/5">
                <button type="button" onclick="closeModal('email-modal')"
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                    <span class="material-symbols-rounded text-[24px]">close</span>
                </button>
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Změna e-mailu</h2>
                <form action="{{ route('admin.account.email') }}" method="POST">
                    @csrf @method('PATCH')
                    <div class="mb-6">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Nový
                            e-mail</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-symbols-rounded text-gray-400 text-[20px]">alternate_email</span>
                            </div>
                            <input type="email" name="email" required value="{{ $admin->email }}"
                                class="w-full rounded-xl border border-gray-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-school-primary focus:border-school-primary bg-white/50 pl-10 py-3 text-sm">
                        </div>
                        @error('email')
                            <div class="flex items-center gap-1 mt-1.5 ml-1 text-school-warning">
                                <span class="material-symbols-rounded text-[16px]">error</span>
                                <p class="text-xs font-medium">{{ $message }}</p>
                            </div>
                        @enderror
                    </div>
                    <button type="submit"
                        class="group relative w-full flex items-center justify-center px-8 py-4 rounded-xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-300 cursor-pointer">
                        <div class="absolute inset-0 topo-bg opacity-50 transition-opacity duration-300"></div>
                        <div
                            class="absolute inset-0 bg-white/60 backdrop-blur-[2px] group-hover:backdrop-blur-[4px] transition-all duration-300">
                        </div>
                        <div class="absolute inset-0 rounded-xl border border-white/60 border-b-4 border-b-gray-200/50">
                        </div>
                        <span class="relative z-10 text-gray-900 font-bold text-base flex items-center drop-shadow-sm">
                            <span
                                class="material-symbols-rounded mr-2 text-[20px] text-gray-600 group-hover:text-school-primary transition-colors">save</span>
                            Uložit změny
                        </span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div id="password-modal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm" onclick="closeModal('password-modal')"></div>
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div
                class="bg-white/90 backdrop-blur-xl rounded-3xl shadow-2xl w-full max-w-md p-8 relative border border-white/60 ring-1 ring-black/5">
                <button type="button" onclick="closeModal('password-modal')"
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                    <span class="material-symbols-rounded text-[24px]">close</span>
                </button>
                <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ $admin->password ? 'Změna hesla' : 'Nastavení hesla' }}
                </h2>
                <form action="{{ route('admin.account.password') }}" method="POST">
                    @csrf @method('PATCH')
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Nové heslo</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-symbols-rounded text-gray-400 text-[20px]">lock</span>
                            </div>
                            <input type="password" name="password" required minlength="8" placeholder="Minimálně 8 znaků"
                                class="w-full rounded-xl border border-gray-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-school-primary focus:border-school-primary bg-white/50 pl-10 py-3 text-sm placeholder-gray-400">
                        </div>
                        @error('password')
                            <div class="flex items-center gap-1 mt-1.5 ml-1 text-school-warning">
                                <span class="material-symbols-rounded text-[16px]">error</span>
                                <p class="text-xs font-medium">{{ $message }}</p>
                            </div>
                        @enderror
                    </div>
                    <div class="mb-6">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Potvrzení
                            hesla</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-symbols-rounded text-gray-400 text-[20px]">lock_reset</span>
                            </div>
                            <input type="password" name="password_confirmation" required minlength="8"
                                placeholder="Zadejte heslo znovu"
                                class="w-full rounded-xl border border-gray-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-school-primary focus:border-school-primary bg-white/50 pl-10 py-3 text-sm placeholder-gray-400">
                        </div>
                    </div>
                    <button type="submit"
                        class="group relative w-full flex items-center justify-center px-8 py-4 rounded-xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-300 cursor-pointer">
                        <div class="absolute inset-0 topo-bg opacity-50 transition-opacity duration-300"></div>
                        <div
                            class="absolute inset-0 bg-white/60 backdrop-blur-[2px] group-hover:backdrop-blur-[4px] transition-all duration-300">
                        </div>
                        <div class="absolute inset-0 rounded-xl border border-white/60 border-b-4 border-b-gray-200/50">
                        </div>
                        <span class="relative z-10 text-gray-900 font-bold text-base flex items-center drop-shadow-sm">
                            <span
                                class="material-symbols-rounded mr-2 text-[20px] text-gray-600 group-hover:text-school-primary transition-colors">save</span>
                            Uložit heslo
                        </span>
                    </button>
                </form>
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
        @if ($errors->has('email'))
            openModal('email-modal');
        @endif
        @if ($errors->has('password'))
            openModal('password-modal');
        @endif
    </script>
@endsection
