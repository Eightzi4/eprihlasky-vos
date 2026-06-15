@extends('layouts.admin')

@section('title', 'Záložní kódy | Administrace OAUH')

@section('content')
    <div class="max-w-xl mx-auto">
        <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-sm border border-white/60 ring-1 ring-black/5 overflow-hidden">
            <div class="px-8 py-5 border-b border-amber-100/80 bg-amber-50/40">
                <div class="flex items-center gap-3">
                    <div class="h-8 w-8 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-rounded text-amber-600 text-[20px]">key</span>
                    </div>
                    <div>
                        <h2 class="font-bold text-gray-800 text-lg">Záložní obnovovací kódy</h2>
                        <p class="text-sm text-amber-700">Uložte si tyto kódy na bezpečné místo.</p>
                    </div>
                </div>
            </div>

            <div class="p-8 space-y-6">
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4">
                    <p class="text-sm text-amber-800 leading-relaxed">
                        <strong>Každý kód lze použít pouze jednou.</strong>
                        Pokud ztratíte přístup k aplikaci Microsoft Authenticator,
                        můžete se pomocí některého z těchto kódů přihlásit.
                        Po použití bude kód zneplatněn.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach ($codes as $code)
                        <div
                            class="bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 font-mono text-sm font-bold text-gray-700 text-center select-all">
                            {{ $code }}
                        </div>
                    @endforeach
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <x-button as="button" text="Stáhnout jako TXT"
                        icon="download" variant="secondary" size="md"
                        extraClass="flex-1" spanClass="text-gray-700"
                        onclick="downloadCodes()" />

                    <x-button as="a" href="{{ route('admin.dashboard') }}" text="Rozumím, pokračovat"
                        icon="check_circle" size="md" extraClass="flex-1" />
                </div>

                <p class="text-xs text-gray-400 text-center">
                    Toto je jediná příležitost, kdy tyto kódy uvidíte. Po odchodu z této stránky již kódy nebude možné zobrazit.
                </p>
            </div>
        </div>
    </div>

    <script>
        function downloadCodes() {
            const codes = @json($codes);
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
@endsection
