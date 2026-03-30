@extends('layouts.application')

@section('form-content')
    @php
        $paymentFile = $application->attachments->where('type', 'payment')->first();
        $serverErrors = array_keys($errors->toArray());
        $serverMessages = collect($errors->toArray())->map(fn($msgs) => $msgs[0])->toArray();
        $isLocked = $application->isPaymentSectionLocked() || $application->payment_accepted;
        $applicationFee = number_format($settings->application_fee, 0, ',', ' ') . ' Kč';
        $bankAccount = $settings->bank_account;
        $vs = $settings->variable_symbol;
    @endphp

    <div x-data="{
        accepted: {{ $application->payment_accepted ? 'true' : 'false' }},
        pending: {{ $application->paid && !$application->payment_accepted ? 'true' : 'false' }}
    }" x-init="window.addEventListener('status-updated', e => {
        accepted = (e.detail.ps === 'complete');
        pending = (e.detail.ps === 'pending');
    })">

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

            <div x-show="pending && !accepted" x-transition
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

                <div class="bg-gray-50 rounded-2xl p-5 mb-6 space-y-3 text-sm border border-gray-100">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Částka</span>
                        <span class="font-bold text-gray-900">{{ $applicationFee }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Číslo účtu</span>
                        <span class="font-bold text-gray-900 font-mono">{{ $bankAccount }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Variabilní symbol</span>
                        <span class="font-bold text-gray-900 font-mono">{{ $vs }}</span>
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
