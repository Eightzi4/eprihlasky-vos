@extends('layouts.app')

@section('title', 'Dvoufázové ověření | OAUH')

@section('header-left')
    <x-button as="a" href="{{ route('admin.login') }}" text="Zpět"
        icon="arrow_back" iconAnimation="back" size="sm" spanClass="text-gray-600" />
@endsection

@section('content')
    <div
        class="w-full max-w-md text-center bg-white/80 backdrop-blur-xl p-10 rounded-3xl shadow-2xl border border-white/60 ring-1 ring-black/5">

        <div class="mb-8 text-center">
            <div class="mx-auto mb-4 h-14 w-14 rounded-2xl bg-red-50 flex items-center justify-center">
                <span class="material-symbols-rounded text-school-primary text-[32px]">phonelink_lock</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Dvoufázové ověření</h1>
            <p class="text-gray-500 text-sm leading-relaxed">
                Otevřete svou aplikaci Microsoft Authenticator a zadejte ověřovací kód.
            </p>
        </div>

        <form action="{{ route('admin.login.two-factor.verify') }}" method="POST" class="space-y-6 text-left">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Ověřovací kód</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="material-symbols-rounded text-gray-400 text-[20px]">pin</span>
                    </div>
                    <input type="text" name="code" required autofocus autocomplete="one-time-code"
                        inputmode="numeric" pattern="[0-9]*" maxlength="6" minlength="6"
                        placeholder="000 000"
                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl leading-5 bg-white/50 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-school-primary focus:border-school-primary sm:text-sm transition-all shadow-sm tracking-[0.5em] text-center text-lg font-bold">
                </div>
                @error('code')
                    <div class="flex items-center gap-1 mt-1.5 ml-1 text-school-warning">
                        <span class="material-symbols-rounded text-[16px]">error</span>
                        <p class="text-xs font-medium">{{ $message }}</p>
                    </div>
                @enderror
            </div>

            <div class="flex flex-col gap-4">
                <x-button as="button" type="submit" text="Ověřit"
                    icon="verified" iconPosition="right"
                    fullWidth size="xl" />
            </div>
        </form>

        <div class="mt-8 pt-6 border-t border-gray-200/60">
            <p class="text-xs text-gray-400 mb-2">Nemůžete se ověřit?</p>
            <p class="text-xs text-gray-500 leading-relaxed">
                Do pole výše můžete zadat také jeden ze
                <strong>záložních obnovovacích kódů</strong>,
                které jste obdrželi při nastavení dvoufázového ověření.
            </p>
        </div>
    </div>
@endsection
