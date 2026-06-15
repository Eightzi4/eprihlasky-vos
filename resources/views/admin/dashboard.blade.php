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

        <div class="grid grid-cols-1 md:grid-cols-3">
            <div class="p-8 border-b md:border-b-0 md:border-r border-gray-200/60 flex flex-col justify-between gap-5">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Kontaktní e-mail</p>
                    <p class="text-lg font-bold text-gray-900 break-all mb-1">{{ $admin->email }}</p>
                    <p class="text-sm text-gray-500">E-mailová adresa, pomocí které se přihlašujete.</p>
                </div>
                <x-button as="button" onclick="openModal('email-modal')"
                    text="Změnit e-mail" icon="edit"
                    variant="secondary" size="md"
                    extraClass="self-start" spanClass="text-gray-700" />
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
                <x-button as="button" onclick="openModal('password-modal')"
                    text="{{ $admin->password ? 'Změnit heslo' : 'Vytvořit heslo' }}" icon="lock_reset"
                    variant="secondary" size="md"
                    extraClass="self-start" spanClass="text-gray-700" />
            </div>

            <div class="p-8 flex flex-col justify-between gap-5">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Dvoufázové ověření</p>
                    @if ($admin->hasTwoFactorEnabled())
                        <div class="flex items-center gap-2 text-green-600 font-bold text-lg">
                            <span class="material-symbols-rounded text-[20px]">verified_user</span>
                            Aktivní
                        </div>
                        <p class="text-sm text-gray-500 mt-1">Účet je chráněn dvoufázovým ověřením.</p>
                    @else
                        <div class="flex items-center gap-2 text-gray-400 font-bold text-lg">
                            <span class="material-symbols-rounded text-[20px]">gpp_maybe</span>
                            Neaktivní
                        </div>
                        <p class="text-sm text-gray-500 mt-1">Doporučujeme aktivovat pro vyšší bezpečnost.</p>
                    @endif
                </div>
                @if ($admin->hasTwoFactorEnabled())
                    <div class="flex flex-col gap-2">
                        <x-button as="button" onclick="openModal('recovery-modal')"
                            text="Záložní kódy" icon="key"
                            variant="secondary" size="md"
                            extraClass="self-start" spanClass="text-gray-700" />
                        <x-button as="button" onclick="openModal('2fa-disable-modal')"
                            text="Deaktivovat 2FA" icon="remove_moderator"
                            variant="ghost" size="sm"
                            extraClass="self-start" spanClass="text-gray-700" />
                    </div>
                @else
                    <x-button as="button" onclick="openModal('2fa-enable-modal')"
                        text="Aktivovat 2FA" icon="verified_user"
                        variant="secondary" size="md"
                        extraClass="self-start" spanClass="text-gray-700" />
                @endif
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
                    <x-button as="button" type="submit" text="Uložit změny"
                        icon="save" fullWidth size="wide" />
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
                    <x-button as="button" type="submit" text="Uložit heslo"
                        icon="save" fullWidth size="wide" />
                </form>
            </div>
        </div>
    </div>

    <div id="2fa-enable-modal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm" onclick="closeModal('2fa-enable-modal')"></div>
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="bg-white/90 backdrop-blur-xl rounded-3xl shadow-2xl w-full max-w-lg p-8 relative border border-white/60 ring-1 ring-black/5 max-h-[90vh] overflow-y-auto">
                <button type="button" onclick="closeModal('2fa-enable-modal')"
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                    <span class="material-symbols-rounded text-[24px]">close</span>
                </button>
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Aktivace dvoufázového ověření</h2>

                @php
                    $enableSecret = session('admin.setup.two_factor_secret');
                    if (! $enableSecret) {
                        $enableSecret = app(\App\Services\TotpService::class)->generateSecret();
                        session()->put('admin.setup.two_factor_secret', $enableSecret);
                    }
                    $enableQrUrl = app(\App\Services\TotpService::class)->generateQrCodeUrl($enableSecret, $admin->email, config('app.name', 'E-prihlaska'));
                    $enableQrImage = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($enableQrUrl);
                @endphp

                <form action="{{ route('admin.account.two-factor.enable') }}" method="POST" class="space-y-6">
                    @csrf
                    <input type="hidden" name="two_factor_secret" value="{{ $enableSecret }}">

                    <p class="text-sm text-gray-500">
                        Naskenujte QR kód pomocí <strong>Microsoft Authenticator</strong> a zadejte ověřovací kód pro potvrzení.
                    </p>

                    <div class="flex justify-center">
                        <div class="bg-white p-3 rounded-2xl border border-gray-200 shadow-sm">
                            <img src="{{ $enableQrImage }}" alt="QR kód" class="w-40 h-40 rounded-xl">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Klíč (ruční zadání)</label>
                        <div class="bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 font-mono text-xs text-gray-700 break-all select-all">
                            {{ $enableSecret }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Ověřovací kód</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-symbols-rounded text-gray-400 text-[20px]">pin</span>
                            </div>
                            <input type="text" name="two_factor_code" required
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

                    <x-button as="button" type="submit" text="Aktivovat dvoufázové ověření"
                        icon="verified_user" fullWidth size="wide" />
                </form>
            </div>
        </div>
    </div>

    <div id="2fa-disable-modal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm" onclick="closeModal('2fa-disable-modal')"></div>
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="bg-white/90 backdrop-blur-xl rounded-3xl shadow-2xl w-full max-w-md p-8 relative border border-white/60 ring-1 ring-black/5">
                <button type="button" onclick="closeModal('2fa-disable-modal')"
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                    <span class="material-symbols-rounded text-[24px]">close</span>
                </button>
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Deaktivace dvoufázového ověření</h2>

                <div class="bg-red-50 border border-red-200 rounded-2xl p-4 mb-6">
                    <p class="text-sm text-red-800 leading-relaxed">
                        <strong>Varování:</strong> Deaktivací dvoufázového ověření snížíte bezpečnost svého účtu.
                        Pro potvrzení zadejte své heslo.
                    </p>
                </div>

                <form action="{{ route('admin.account.two-factor.disable') }}" method="POST">
                    @csrf @method('DELETE')
                    <div class="mb-6">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Heslo</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-symbols-rounded text-gray-400 text-[20px]">lock</span>
                            </div>
                            <input type="password" name="password" required
                                placeholder="Zadejte své heslo"
                                class="w-full rounded-xl border border-gray-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-school-primary focus:border-school-primary bg-white/50 pl-10 py-3 text-sm placeholder-gray-400">
                        </div>
                        @error('password')
                            <div class="flex items-center gap-1 mt-1.5 ml-1 text-school-warning">
                                <span class="material-symbols-rounded text-[16px]">error</span>
                                <p class="text-xs font-medium">{{ $message }}</p>
                            </div>
                        @enderror
                    </div>

                    <x-button as="button" type="submit" text="Deaktivovat"
                        icon="remove_moderator" fullWidth size="wide" />
                </form>
            </div>
        </div>
    </div>

    @if (session('show_recovery_modal') && session('recovery_codes'))
        <div id="recovery-modal" class="fixed inset-0 z-50" x-data="{ show: true }" x-show="show">
            <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"></div>
            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="bg-white/90 backdrop-blur-xl rounded-3xl shadow-2xl w-full max-w-lg p-8 relative border border-white/60 ring-1 ring-black/5">
                    <div class="mb-6">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="h-8 w-8 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
                                <span class="material-symbols-rounded text-amber-600 text-[20px]">key</span>
                            </div>
                            <h2 class="font-bold text-gray-800 text-lg">Záložní obnovovací kódy</h2>
                        </div>
                        <p class="text-sm text-amber-700">Uložte si tyto kódy na bezpečné místo. Každý kód lze použít pouze jednou.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6">
                        @foreach (session('recovery_codes') as $code)
                            <div class="bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 font-mono text-sm font-bold text-gray-700 text-center select-all">
                                {{ $code }}
                            </div>
                        @endforeach
                    </div>

                    <div class="flex gap-3">
                        <x-button as="button" text="Stáhnout TXT"
                            icon="download" variant="secondary" size="md" extraClass="flex-1" spanClass="text-gray-700"
                            onclick="downloadRecoveryCodes()" />
                        <x-button as="a" href="{{ route('admin.dashboard') }}" text="Hotovo"
                            icon="check_circle" size="md" extraClass="flex-1" />
                    </div>

                    <p class="text-xs text-gray-400 text-center mt-4">
                        Toto je jediná příležitost, kdy tyto kódy uvidíte.
                    </p>
                </div>
            </div>
        </div>

        <script>
            function downloadRecoveryCodes() {
                const codes = @json(session('recovery_codes'));
                const text = 'Záložní obnovovací kódy – E-přihláška OAUH\n' +
                    'Vygenerováno: ' + new Date().toLocaleString('cs-CZ') + '\n\n' +
                    codes.join('\n') +
                    '\n\nKaždý kód lze použít pouze jednou.\n';
                const blob = new Blob([text], { type: 'text/plain;charset=utf-8' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'zalozni-kody-' + new Date().toISOString().slice(0, 10) + '.txt';
                a.click();
                URL.revokeObjectURL(url);
            }
        </script>
    @endif

    <div id="recovery-modal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm" onclick="closeModal('recovery-modal')"></div>
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="bg-white/90 backdrop-blur-xl rounded-3xl shadow-2xl w-full max-w-md p-8 relative border border-white/60 ring-1 ring-black/5">
                <button type="button" onclick="closeModal('recovery-modal')"
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                    <span class="material-symbols-rounded text-[24px]">close</span>
                </button>
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Záložní obnovovací kódy</h2>

                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 mb-6">
                    <p class="text-sm text-amber-800">
                        Vygenerováním nových kódů zneplatníte všechny předchozí. Každý kód lze použít pouze jednou.
                    </p>
                </div>

                <form action="{{ route('admin.account.two-factor.recovery') }}" method="POST" class="space-y-4">
                    @csrf
                    <x-button as="button" type="submit" text="Vygenerovat nové kódy"
                        icon="refresh" fullWidth size="wide" />
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
        @if ($errors->has('two_factor_code'))
            openModal('2fa-enable-modal');
        @endif
    </script>
@endsection
