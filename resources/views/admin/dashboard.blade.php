@extends('layouts.admin')

@section('title', 'Přehled | Administrace OAUH')

@section('content')
    @php $admin = Auth::guard('admin')->user(); @endphp

    @if ($admin->isMainAdmin())
        <div class="flex items-center justify-end mb-2">
            <x-button as="button" onclick="openModal('presets-modal')"
                text="Spravovat panely" icon="tune"
                variant="ghost" size="sm" spanClass="text-gray-500" />
        </div>
    @endif

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        @foreach ($presets as $preset)
            @php
                $params = [
                    'cp' => $preset['checkpoint'] ?? '',
                    'st' => $preset['state'] ?? '',
                ];
                if ($preset['study_program_id']) {
                    $params['program'] = $preset['study_program_id'];
                }
                if ($preset['round_id']) {
                    $params['round'] = $preset['round_id'];
                }
            @endphp
            <a href="{{ route('admin.applications', $params) }}"
                class="block bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-white/60 ring-1 ring-black/5 p-5 hover:shadow-md hover:ring-school-primary/20 hover:border-school-primary/20 transition-all duration-200 group">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wide group-hover:text-gray-500 transition-colors">{{ $preset['label'] }}</p>
                    <span class="material-symbols-rounded {{ $preset['color_class'] }} text-[22px]">{{ $preset['icon'] }}</span>
                </div>
                <p class="text-3xl font-bold text-gray-900">{{ $preset['count'] }}</p>
            </a>
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

    @if ($admin->isMainAdmin())
        <div id="presets-modal" class="fixed inset-0 z-50 hidden">
            <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm" onclick="closeModal('presets-modal')"></div>
            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="bg-white/90 backdrop-blur-xl rounded-3xl shadow-2xl w-full max-w-2xl p-8 relative border border-white/60 ring-1 ring-black/5 max-h-[90vh] overflow-y-auto">
                    <button type="button" onclick="closeModal('presets-modal')"
                        class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                        <span class="material-symbols-rounded text-[24px]">close</span>
                    </button>
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Spravovat panely</h2>

                    <div class="space-y-2 mb-8" id="presets-list">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Existující panely <span class="text-gray-300 font-normal">— přetáhněte pro změnu pořadí</span></p>
                        @foreach ($presets as $preset)
                            <div class="flex items-center gap-3 bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 preset-item cursor-grab active:cursor-grabbing transition-shadow hover:shadow-sm"
                                draggable="true"
                                data-preset-id="{{ $preset['id'] }}"
                                ondragstart="handleDragStart(event)"
                                ondragend="handleDragEnd(event)"
                                ondragover="handleDragOver(event)"
                                ondrop="handleDrop(event)">
                                <span class="material-symbols-rounded text-gray-300 text-[20px] flex-shrink-0 cursor-grab drag-handle">drag_indicator</span>
                                <span class="material-symbols-rounded {{ $preset['color_class'] }} text-[20px] flex-shrink-0">{{ $preset['icon'] }}</span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-gray-800 truncate">{{ $preset['label'] }}</p>
                                    <p class="text-xs text-gray-400 truncate">
                                        @if ($preset['checkpoint'])
                                            {{ $preset['checkpoint'] }} &rarr; {{ $preset['state'] }}
                                        @else
                                            bez filtru
                                        @endif
                                    </p>
                                </div>
                                <span class="text-xs font-bold text-gray-400 w-10 text-right">{{ $preset['count'] }}</span>
                                <div class="flex items-center gap-1 flex-shrink-0">
                                    <button onclick="editPreset({{ $preset['id'] }}, {{ Js::from($preset) }})"
                                        class="p-1.5 text-gray-400 hover:text-school-primary hover:bg-red-50 rounded-lg transition-colors"
                                        title="Upravit">
                                        <span class="material-symbols-rounded text-[16px]">edit</span>
                                    </button>
                                    <form action="{{ route('admin.dashboard-presets.destroy', $preset['id']) }}" method="POST"
                                        onsubmit="return confirm('Opravdu odstranit panel \u201e{{ $preset['label'] }}\u201c?')" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors"
                                            title="Odstranit">
                                            <span class="material-symbols-rounded text-[16px]">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="border-t border-gray-200 pt-6">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-4" id="preset-form-title">Přidat panel</p>
                        <form id="preset-form" action="{{ route('admin.dashboard-presets.store') }}" method="POST" class="space-y-4">
                            @csrf
                            <input type="hidden" name="_method" id="preset-method" value="POST">

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Popisek</label>
                                    <input type="text" name="label" id="preset-label" required
                                        placeholder="např. Čeká na schválení platby"
                                        class="w-full rounded-xl border border-gray-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-school-primary focus:border-school-primary bg-white/50 px-3 py-2 text-sm placeholder-gray-400">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Ikona <span class="text-gray-400 font-normal">(Material Symbol)</span></label>
                                    <input type="text" name="icon" id="preset-icon" required
                                        placeholder="např. payments"
                                        class="w-full rounded-xl border border-gray-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-school-primary focus:border-school-primary bg-white/50 px-3 py-2 text-sm placeholder-gray-400">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Barva ikony</label>
                                <div class="flex items-center gap-3 flex-wrap">
                                    @foreach ([
                                        'text-gray-500' => 'Šedá',
                                        'text-green-500' => 'Zelená',
                                        'text-amber-500' => 'Jantarová',
                                        'text-blue-500' => 'Modrá',
                                        'text-school-primary' => 'Červená',
                                    ] as $cls => $name)
                                        <label class="flex items-center gap-1.5 cursor-pointer">
                                            <input type="radio" name="color_class" value="{{ $cls }}"
                                                id="preset-color-{{ $loop->index }}"
                                                {{ $cls === 'text-gray-500' ? 'checked' : '' }}>
                                            <span class="text-xs text-gray-600">{{ $name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Krok (checkpoint)</label>
                                    <select name="checkpoint" id="preset-checkpoint"
                                        class="w-full rounded-xl border border-gray-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-school-primary focus:border-school-primary bg-white/50 px-3 py-2 text-sm appearance-none">
                                        <option value="">Bez filtru (všechny)</option>
                                        <option value="identity_verified">Ověření identity</option>
                                        <option value="step1">Osobní údaje</option>
                                        <option value="gdpr_accepted">Souhlas GDPR</option>
                                        <option value="submitted">Přihláška odeslána</option>
                                        <option value="step2">Vzdělání</option>
                                        <option value="payment">Platba</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Stav</label>
                                    <select name="state" id="preset-state"
                                        class="w-full rounded-xl border border-gray-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-school-primary focus:border-school-primary bg-white/50 px-3 py-2 text-sm appearance-none">
                                        <option value="">Vyberte stav</option>
                                        <option value="complete">Splněno</option>
                                        <option value="incomplete">Nesplněno</option>
                                        <option value="pending">Čeká na schválení</option>
                                        <option value="failed">Nesplněno po termínu</option>
                                        <option value="locked">Uzamčeno</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Obor <span class="text-gray-400 font-normal">(volitelné)</span></label>
                                    <select name="study_program_id" id="preset-program"
                                        class="w-full rounded-xl border border-gray-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-school-primary focus:border-school-primary bg-white/50 px-3 py-2 text-sm appearance-none">
                                        <option value="">Všechny obory</option>
                                        @foreach (\App\Models\StudyProgram::orderBy('name')->get() as $sp)
                                            <option value="{{ $sp->id }}">{{ $sp->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Kolo <span class="text-gray-400 font-normal">(volitelné)</span></label>
                                    <select name="round_id" id="preset-round"
                                        class="w-full rounded-xl border border-gray-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-school-primary focus:border-school-primary bg-white/50 px-3 py-2 text-sm appearance-none">
                                        <option value="">Všechna kola</option>
                                        @foreach (\App\Models\ApplicationRound::with('studyProgram')->orderBy('academic_year')->orderBy('label')->get() as $r)
                                            <option value="{{ $r->id }}">{{ $r->label ?? $r->academic_year }} — {{ $r->studyProgram?->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 pt-2">
                                <x-button as="button" type="submit" text="Přidat panel"
                                    icon="add" iconAnimation="rotate" id="preset-submit-btn" />
                                <button type="button" onclick="resetPresetForm()"
                                    id="preset-cancel-btn"
                                    class="text-xs font-semibold text-gray-500 hover:text-gray-700 hidden">
                                    Zrušit úpravu
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script>
            let dragSrcEl = null;

            function handleDragStart(e) {
                dragSrcEl = e.currentTarget;
                e.currentTarget.classList.add('opacity-50', 'ring-2', 'ring-school-primary/30');
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', e.currentTarget.dataset.presetId);
            }

            function handleDragEnd(e) {
                e.currentTarget.classList.remove('opacity-50', 'ring-2', 'ring-school-primary/30');
                document.querySelectorAll('.preset-item').forEach(el => {
                    el.classList.remove('border-school-primary', 'bg-red-50/30');
                });
                dragSrcEl = null;
            }

            function handleDragOver(e) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                const target = e.currentTarget;
                if (target !== dragSrcEl) {
                    target.classList.add('border-school-primary', 'bg-red-50/30');
                }
            }

            async function handleDrop(e) {
                e.preventDefault();
                e.stopPropagation();
                const target = e.currentTarget;
                target.classList.remove('border-school-primary', 'bg-red-50/30');

                if (dragSrcEl && dragSrcEl !== target) {
                    const list = document.getElementById('presets-list');
                    const items = [...list.querySelectorAll('.preset-item')];
                    const srcIndex = items.indexOf(dragSrcEl);
                    const targetIndex = items.indexOf(target);

                    if (srcIndex < targetIndex) {
                        list.insertBefore(dragSrcEl, target.nextSibling);
                    } else {
                        list.insertBefore(dragSrcEl, target);
                    }

                    const newOrder = [...list.querySelectorAll('.preset-item')].map(el => el.dataset.presetId);

                    const resp = await fetch('{{ route('admin.dashboard-presets.reorder') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ order: newOrder }),
                    });

                    if (resp.ok) {
                        window.location.reload();
                    }
                }
            }

            function editPreset(id, preset) {
                const form = document.getElementById('preset-form');
                form.action = '{{ route('admin.dashboard-presets.update', ['dashboardPreset' => '__ID__']) }}'.replace('__ID__', id);
                document.getElementById('preset-method').value = 'PATCH';
                document.getElementById('preset-label').value = preset.label;
                document.getElementById('preset-icon').value = preset.icon;
                document.getElementById('preset-checkpoint').value = preset.checkpoint || '';
                document.getElementById('preset-state').value = preset.state || '';
                document.getElementById('preset-program').value = preset.study_program_id || '';
                document.getElementById('preset-round').value = preset.round_id || '';
                document.querySelector('input[name="color_class"][value="' + (preset.color_class || 'text-gray-500') + '"]').checked = true;
                document.getElementById('preset-form-title').textContent = 'Upravit panel';
                document.getElementById('preset-submit-btn').querySelector('span').textContent = 'Uložit změny';
                document.getElementById('preset-cancel-btn').classList.remove('hidden');
                openModal('presets-modal');
                document.getElementById('preset-form').scrollIntoView({ behavior: 'smooth' });
            }

            function resetPresetForm() {
                const form = document.getElementById('preset-form');
                form.action = '{{ route('admin.dashboard-presets.store') }}';
                document.getElementById('preset-method').value = 'POST';
                document.getElementById('preset-label').value = '';
                document.getElementById('preset-icon').value = '';
                document.getElementById('preset-checkpoint').value = '';
                document.getElementById('preset-state').value = '';
                document.getElementById('preset-program').value = '';
                document.getElementById('preset-round').value = '';
                document.querySelector('input[name="color_class"][value="text-gray-500"]').checked = true;
                document.getElementById('preset-form-title').textContent = 'Přidat panel';
                document.getElementById('preset-submit-btn').querySelector('span').textContent = 'Přidat panel';
                document.getElementById('preset-cancel-btn').classList.add('hidden');
            }
        </script>
    @endif

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
