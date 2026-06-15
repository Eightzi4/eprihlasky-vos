@extends('layouts.admin')

@section('title', 'Nastavení účtu | Administrace OAUH')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-sm border border-white/60 ring-1 ring-black/5 overflow-hidden">
            <div class="px-8 py-5 border-b border-gray-100/80 bg-white/40">
                <div class="flex items-center gap-3">
                    <div class="h-8 w-8 rounded-lg bg-red-50 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-rounded text-school-primary text-[20px]">admin_panel_settings</span>
                    </div>
                    <div>
                        <h2 class="font-bold text-gray-800 text-lg">Nastavení administrátorského účtu</h2>
                        <p class="text-sm text-gray-400">Před prvním použitím si prosím nastavte heslo a dvoufázové ověření.</p>
                    </div>
                </div>
            </div>

            <div class="p-8 space-y-8">
                <form action="{{ route('admin.setup.store') }}" method="POST" class="space-y-8">
                    @csrf
                    <input type="hidden" name="two_factor_secret" value="{{ $secret }}">

                    <div class="space-y-5">
                        <div class="flex items-center gap-2">
                            <span
                                class="flex items-center justify-center w-6 h-6 rounded-full bg-school-primary text-white text-xs font-bold">1</span>
                            <h3 class="font-bold text-gray-800 text-base">Nastavte si heslo</h3>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
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
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Potvrzení hesla</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-symbols-rounded text-gray-400 text-[20px]">lock_reset</span>
                                    </div>
                                    <input type="password" name="password_confirmation" required minlength="8"
                                        placeholder="Zadejte heslo znovu"
                                        class="w-full rounded-xl border border-gray-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-school-primary focus:border-school-primary bg-white/50 pl-10 py-3 text-sm placeholder-gray-400">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-5">
                        <div class="flex items-center gap-2">
                            <span
                                class="flex items-center justify-center w-6 h-6 rounded-full bg-school-primary text-white text-xs font-bold">2</span>
                            <h3 class="font-bold text-gray-800 text-base">Nastavte dvoufázové ověření</h3>
                        </div>

                        <p class="text-sm text-gray-500">
                            Naskenujte QR kód pomocí aplikace <strong>Microsoft Authenticator</strong>
                            (nebo jiné TOTP aplikace jako Google Authenticator, Authy).
                        </p>

                        <div class="flex flex-col sm:flex-row items-center gap-8">
                            <div class="flex-shrink-0 bg-white p-3 rounded-2xl border border-gray-200 shadow-sm">
                                <img src="{{ $qrImageUrl }}" alt="QR kód pro nastavení dvoufázového ověření"
                                    class="w-48 h-48 rounded-xl">
                            </div>

                            <div class="flex-1 space-y-3 text-sm text-gray-500">
                                <p>Pokud nemůžete naskenovat QR kód, zadejte do aplikace tento klíč ručně:</p>
                                <div
                                    class="bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 font-mono text-xs text-gray-700 break-all select-all">
                                    {{ $secret }}
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Ověřovací kód z
                                        aplikace</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="material-symbols-rounded text-gray-400 text-[20px]">pin</span>
                                        </div>
                                        <input type="text" name="two_factor_code" required autofocus
                                            inputmode="numeric" pattern="[0-9]*" maxlength="6" minlength="6"
                                            placeholder="000 000" autocomplete="one-time-code"
                                            class="w-full rounded-xl border border-gray-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-school-primary focus:border-school-primary bg-white/50 pl-10 py-3 text-sm placeholder-gray-400 tracking-[0.5em] text-center font-bold">
                                    </div>
                                    @error('two_factor_code')
                                        <div class="flex items-center gap-1 mt-1.5 ml-1 text-school-warning">
                                            <span class="material-symbols-rounded text-[16px]">error</span>
                                            <p class="text-xs font-medium">{{ $message }}</p>
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <x-button as="button" type="submit" text="Dokončit nastavení"
                        icon="check_circle" fullWidth size="xl" />
                </form>
            </div>
        </div>
    </div>
@endsection
