@extends('layouts.application')

@section('form-content')
    @php
        $paymentFile = $application->attachments->where('type', 'payment')->first();
        $serverErrors = array_keys($errors->toArray());
        $serverMessages = collect($errors->toArray())->map(fn($msgs) => $msgs[0])->toArray();
        $isLocked = $application->isPaymentSectionLocked() || $application->payment_accepted;
        $applicationFee = number_format($settings->application_fee, 0, ',', ' ') . ' Kč';
        $bankAccount = $settings->bank_account;
        $iban = strtoupper(str_replace(' ', '', $bankAccount));
        $displayAccount = $iban;
        if (preg_match('/^CZ\d{22}$/', $iban)) {
            $bank    = substr($iban, 4, 4);
            $prefix  = ltrim(substr($iban, 8, 6), '0');
            $account = ltrim(substr($iban, 14, 10), '0');
            $displayAccount = $prefix ? "{$prefix}-{$account}/{$bank}" : "{$account}/{$bank}";
        }

        $vs = preg_replace('/\D/', '', (string) $variableSymbol);
        $amount = (int) $settings->application_fee;
        $spd = 'SPD*1.0*ACC:' . $iban . '*AM:' . $amount . '*CC:CZK*X-VS:' . $vs;
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' . urlencode($spd);
    @endphp

    <div x-data="{
        accepted: {{ $application->payment_accepted ? 'true' : 'false' }},
        pending: {{ $application->paid && !$application->payment_accepted ? 'true' : 'false' }},
        hasMessage: {{ $application->payment_admin_message && !$application->payment_accepted ? 'true' : 'false' }}
    }" x-init="window.addEventListener('status-updated', e => {
        accepted = (e.detail.ps === 'complete');
        pending = (e.detail.ps === 'pending');
    });
    window.addEventListener('file-uploaded', () => {
        hasMessage = false;
    });">

        <div x-data="stepValidator({
            step: 4,
            serverErrorFields: @json($serverErrors),
            serverMessages: @json($serverMessages),
            fields: [
                { name: 'payment_file', message: 'Potvrzení o platbě je povinné.' },
            ]
        })">
            <div x-show="accepted" x-transition
                class="bg-green-50 border border-green-200 rounded-3xl p-5 flex items-center gap-4 mb-6">
                <span class="material-symbols-rounded text-green-500 text-[28px] flex-shrink-0">verified</span>
                <div>
                    <p class="font-bold text-green-900 text-sm">Platba byla přijata</p>
                    <p class="text-xs text-green-700 mt-0.5">Vaše platba byla ověřena školou.</p>
                </div>
            </div>

            <div x-show="hasMessage" x-transition
                class="bg-amber-50 border border-amber-200 rounded-3xl p-5 flex items-start gap-4 mb-6">
                <span class="material-symbols-rounded text-amber-500 text-[28px] flex-shrink-0">rate_review</span>
                <div>
                    <p class="font-bold text-amber-900 text-sm">Škola požaduje úpravu</p>
                    <p class="text-sm text-amber-800 mt-1 leading-relaxed">{{ $application->payment_admin_message }}</p>
                    <p class="text-xs text-amber-600 mt-2">Po úpravě prosím nahrajte doklad znovu.</p>
                </div>
            </div>

            <div x-show="pending && !accepted && !hasMessage" x-transition
                class="bg-blue-50 border border-blue-200 rounded-3xl p-5 flex items-center gap-4 mb-6">
                <span class="material-symbols-rounded text-blue-400 text-[28px] flex-shrink-0">hourglass_top</span>
                <div>
                    <p class="font-bold text-blue-900 text-sm">Platba čeká na ověření</p>
                    <p class="text-xs text-blue-700 mt-0.5">Potvrzení bylo nahráno. Vyčkejte na ověření školou.</p>
                </div>
            </div>

            <div
                class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-sm border border-white/60 p-6 sm:p-8 ring-1 ring-black/5 mb-6">
                <div class="mb-6">
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Platba přihlášky</h2>
                    <p class="text-sm text-gray-500 mt-1">
                        Po uhrazení přihlášky nahrajte potvrzení o platbě. Přihláška bude plně zpracována po ověření platby
                        školou.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-6 mb-6">
                    <div class="flex-1 space-y-3">
                        <div>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wide">Částka</span>
                            <p class="text-lg font-bold text-gray-900 font-mono">{{ $applicationFee }}</p>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wide">Číslo účtu</span>
                            <p class="text-base font-bold text-gray-900 font-mono">{{ $displayAccount }}</p>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wide">Variabilní symbol</span>
                            <p class="text-base font-bold text-gray-900 font-mono">{{ $vs }}</p>
                        </div>
                    </div>

                    <div class="flex-shrink-0 flex flex-col items-center">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">QR platba</p>
                        <img src="{{ $qrUrl }}" alt="QR platba" class="w-40 h-40 rounded-xl bg-white p-1.5 border border-gray-200 shadow-sm">
                        <p class="text-[10px] text-gray-400 mt-2 text-center leading-tight">Naskenujte v mobilním<br>bankovnictví</p>
                    </div>
                </div>

                <x-file-uploader field-name="payment_file" :saved-files="$paymentFile ? [$paymentFile] : []" :locked="$isLocked" :required="true"
                    required-message="Potvrzení o platbě je povinné." />
            </div>

            <x-step-footer :application="$application" prev-route="application.step3" prev-label="Zpět na přílohy"
                next-route="application.step5" next-label="Přejít na souhrn" />
        </div>
    </div>
@endsection
