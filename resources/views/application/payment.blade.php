@extends('layouts.application')

@section('form-content')
    @php
        $paymentFile = $application->attachments->where('type', 'payment')->first();
        $isLocked = $application->isPaymentSectionLocked();
        $program = $application->studyProgram;
        $tuition = $program->tuition_fee ?? '—';
        $bankAccount = config('school.bank_account', '1234567890/0800');
        $vs = $application->application_number ?? $application->id;
    @endphp

    @if ($application->payment_accepted)
        <div class="bg-green-50 border border-green-200 rounded-3xl p-5 flex items-center gap-4 mb-6">
            <span class="material-symbols-rounded text-green-500 text-[28px] flex-shrink-0">verified</span>
            <div>
                <p class="font-bold text-green-900 text-sm">Platba byla přijata</p>
                <p class="text-xs text-green-700 mt-0.5">Vaše platba byla ověřena školou. Přihláška je kompletní.</p>
            </div>
        </div>
    @elseif ($application->paid)
        <div class="bg-blue-50 border border-blue-200 rounded-3xl p-5 flex items-center gap-4 mb-6">
            <span class="material-symbols-rounded text-blue-400 text-[28px] flex-shrink-0">hourglass_top</span>
            <div>
                <p class="font-bold text-blue-900 text-sm">Platba čeká na ověření</p>
                <p class="text-xs text-blue-700 mt-0.5">Potvrzení bylo nahráno. Vyčkejte na ověření školou.</p>
            </div>
        </div>
    @endif

    <div
        class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-sm border border-white/60 p-6 sm:p-8 ring-1 ring-black/5 mb-6">
        <div class="mb-6">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Platba zápisného</h2>
            <p class="text-sm text-gray-500 mt-1">
                Po uhrazení zápisného nahrajte potvrzení o platbě. Přihláška bude plně zpracována po ověření platby školou.
            </p>
        </div>

        <div class="bg-gray-50 rounded-2xl p-5 mb-6 space-y-3 text-sm border border-gray-100">
            <div class="flex justify-between">
                <span class="text-gray-500">Částka</span>
                <span class="font-bold text-gray-900">{{ $tuition }}</span>
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

        @if ($isLocked)
            <div
                class="flex items-center gap-3 p-4 bg-gray-50 rounded-2xl border border-gray-200 text-sm text-gray-500 mb-4">
                <span class="material-symbols-rounded text-gray-400 text-[20px]">lock</span>
                Tato sekce je uzamčena a nelze ji upravovat.
            </div>
        @endif

        <x-file-uploader field-name="payment_file" :saved-files="$paymentFile ? [$paymentFile] : []" :locked="$isLocked" />
    </div>

    <x-step-footer :application="$application" prev-route="application.step3" prev-label="Zpět na přílohy"
        next-route="application.step5" next-label="Přejít na souhrn" />
@endsection
