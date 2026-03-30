@extends('layouts.application')

@section('form-content')
    @php
        $otherFiles = $application->attachments->where('type', 'other');
        $isLocked = $application->isStepLocked(3);
    @endphp

    <x-form-section title="Specifické potřeby"
        description="Uveďte, pokud vyžadujete specifický přístup (např. zdravotní znevýhodnění, poruchy učení).">
        <x-form-field name="specific_needs" type="textarea" label="" :span="2"
            placeholder="Zde popište své požadavky..." :value="old('specific_needs', $application->specific_needs)" :locked="$isLocked" />
    </x-form-section>

    <x-form-section title="Poznámka k přihlášce"
        description="Prostor pro jakékoliv další informace, které nám chcete sdělit (např. adresa pro zaslání výsledků přijímacího řízení, pokud se neshoduje s adresou trvalého bydliště).">
        <x-form-field name="note" type="textarea" label="" :span="2" placeholder="Vaše poznámka..."
            :value="old('note', $application->note)" :locked="$isLocked" />
    </x-form-section>

    <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-sm border border-white/60 p-8 ring-1 ring-black/5 mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Další přílohy</h2>
        <p class="text-sm text-gray-500 mb-6">Zde můžete nahrát další dokumenty (např. certifikáty, potvrzení od lékaře).
        </p>
        <x-file-uploader field-name="other_files[]" :saved-files="$otherFiles->values()->all()" :multiple="true" :locked="$isLocked" />
    </div>

    <x-step-footer :application="$application" prev-route="application.step2" prev-label="Zpět na vzdělání"
        next-route="application.step4" next-label="Přejít na platbu" />
@endsection
