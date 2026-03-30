@extends('layouts.admin')

@section('title', 'Nastavení | Administrace OAUH')

@section('content')
    <div
        class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-sm border border-white/60 ring-1 ring-black/5 overflow-hidden">
        <div class="px-8 py-5 border-b border-gray-100/80 bg-white/40 flex items-center gap-3">
            <div class="h-8 w-8 rounded-lg bg-red-50 flex items-center justify-center flex-shrink-0">
                <span class="material-symbols-rounded text-school-primary text-[20px]">settings</span>
            </div>
            <div>
                <h2 class="font-bold text-gray-800 text-lg">Nastavení webu</h2>
                <p class="text-sm text-gray-500">Sdílené údaje pro přihlášku a e-mailové notifikace.</p>
            </div>
        </div>

        <form action="{{ route('admin.settings.update') }}" method="POST" class="p-8 space-y-6">
            @csrf
            @method('PATCH')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                @foreach ([
            ['name' => 'application_fee', 'label' => 'Cena přihlášky', 'icon' => 'payments', 'type' => 'number', 'required' => true, 'value' => old('application_fee', $settings->application_fee), 'attributes' => 'min=0 step=1'],
            ['name' => 'notification_email', 'label' => 'Notifikační e-mail', 'icon' => 'alternate_email', 'type' => 'email', 'required' => true, 'value' => old('notification_email', $settings->notification_email)],
            ['name' => 'bank_account', 'label' => 'Číslo účtu', 'icon' => 'account_balance', 'type' => 'text', 'required' => true, 'value' => old('bank_account', $settings->bank_account)],
            ['name' => 'variable_symbol', 'label' => 'Variabilní symbol', 'icon' => 'tag', 'type' => 'text', 'required' => true, 'value' => old('variable_symbol', $settings->variable_symbol)],
            ['name' => 'applicant_notification_delay_minutes', 'label' => 'Prodleva notifikace uchazeči (min)', 'icon' => 'schedule_send', 'type' => 'number', 'required' => true, 'value' => old('applicant_notification_delay_minutes', $settings->applicant_notification_delay_minutes), 'attributes' => 'min=0 step=1'],
        ] as $field)
                    <div class="{{ $field['name'] === 'applicant_notification_delay_minutes' ? 'md:col-span-2' : '' }}">
                        <label for="{{ $field['name'] }}"
                            class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">{{ $field['label'] }}</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-symbols-rounded text-gray-400 text-[20px]">{{ $field['icon'] }}</span>
                            </div>
                            <input id="{{ $field['name'] }}" type="{{ $field['type'] }}" name="{{ $field['name'] }}"
                                value="{{ $field['value'] }}" {{ !empty($field['required']) ? 'required' : '' }}
                                {{ $field['attributes'] ?? '' }}
                                class="w-full rounded-xl border border-gray-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-school-primary focus:border-school-primary bg-white/50 pl-10 py-3 text-sm placeholder-gray-400">
                        </div>
                        @error($field['name'])
                            <div class="flex items-center gap-1 mt-1.5 ml-1 text-school-warning">
                                <span class="material-symbols-rounded text-[16px]">error</span>
                                <p class="text-xs font-medium">{{ $message }}</p>
                            </div>
                        @enderror
                    </div>
                @endforeach
            </div>

            <button type="submit"
                class="group relative w-full md:w-auto flex items-center justify-center px-8 py-4 rounded-xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-300 cursor-pointer">
                <div class="absolute inset-0 topo-bg opacity-50 transition-opacity duration-300"></div>
                <div
                    class="absolute inset-0 bg-white/60 backdrop-blur-[2px] group-hover:backdrop-blur-[4px] transition-all duration-300">
                </div>
                <div class="absolute inset-0 rounded-xl border border-white/60 border-b-4 border-b-gray-200/50"></div>
                <span class="relative z-10 text-gray-900 font-bold text-base flex items-center drop-shadow-sm">
                    <span
                        class="material-symbols-rounded mr-2 text-[20px] text-gray-600 group-hover:text-school-primary transition-colors">save</span>
                    Uložit nastavení
                </span>
            </button>
        </form>
    </div>
@endsection
