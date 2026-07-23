@extends('layouts.app')

@section('title', 'Přihlášení | E-přihláška OAUH')

@section('header-left')
    <x-button as="a" href="{{ url('/') }}" text="Zpět"
        icon="arrow_back" iconAnimation="back" size="sm" spanClass="text-gray-600" />
@endsection

@section('content')
    <div
        class="w-full max-w-md text-center bg-white/80 backdrop-blur-xl p-10 rounded-3xl shadow-2xl border border-white/60 ring-1 ring-black/5">

        <div class="mb-8 text-center">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">
                Vstup do systému
            </h1>
            <p class="text-gray-500 text-sm">
                Zadejte svůj e-mail pro přihlášení nebo registraci.
            </p>
        </div>



        <form action="{{ route('auth.email') }}" method="POST" class="space-y-6 text-left">
            @csrf

            <div style="position: absolute; left: -9999px;" aria-hidden="true">
                <label for="website">Website</label>
                <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
            </div>

            <input type="hidden" name="timestamp" value="{{ now()->timestamp }}">

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">
                    E-mailová adresa
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="material-symbols-rounded text-gray-400 text-[20px]">alternate_email</span>
                    </div>
                    <input type="email" name="email" id="email" required
                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl leading-5 bg-white/50 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-school-primary focus:border-school-primary sm:text-sm transition-all shadow-sm"
                        placeholder="jan.novak@example.com">
                </div>
            </div>

            <x-button as="button" type="submit" text="Pokračovat"
                icon="arrow_forward" iconPosition="right" iconAnimation="forward"
                fullWidth size="wide" />
        </form>

        <div class="mt-8 pt-6 border-t border-gray-200/60 text-xs text-gray-400">
            Pokud u nás ještě nemáte účet, vytvoříme vám ho automaticky.
        </div>
    </div>
@endsection
