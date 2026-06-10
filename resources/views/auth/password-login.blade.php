@extends('layouts.app')

@section('title', 'Přihlášení heslem | VOŠ OAUH')

@section('header-left')
    <x-button as="a" href="{{ route('login') }}" text="Zpět"
        icon="arrow_back" iconAnimation="back" size="sm" spanClass="text-gray-600" />
@endsection

@section('content')
    <div
        class="w-full max-w-md text-center bg-white/80 backdrop-blur-xl p-10 rounded-3xl shadow-2xl border border-white/60 ring-1 ring-black/5">

        <div class="mb-8 text-center">
            <h1 class="text-2xl font-bold text-gray-900 mb-4 leading-tight">
                Přihlášení do elektronické přihlášky
            </h1>
            <p class="text-gray-500 text-sm leading-relaxed">
                Přihlašte se zadáním hesla. V případě ztráty hesla si nechte zaslat nový přístupový odkaz e-mailem.
            </p>
        </div>

        <form action="{{ route('auth.password') }}" method="POST" class="space-y-6 text-left">
            @csrf

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">
                    E-mailová adresa
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="material-symbols-rounded text-gray-400 text-[20px]">alternate_email</span>
                    </div>
                    <input type="hidden" name="email" value="{{ $email }}">
                    <input type="text" value="{{ $email }}" disabled
                        class="block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-xl leading-5 bg-gray-50 text-gray-500 cursor-not-allowed sm:text-sm">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">
                    Heslo
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="material-symbols-rounded text-gray-400 text-[20px]">lock</span>
                    </div>
                    <input type="password" name="password" required autofocus
                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl leading-5 bg-white/50 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-school-primary focus:border-school-primary sm:text-sm transition-all shadow-sm">
                </div>
                @error('password')
                    <div class="flex items-center gap-1 mt-1.5 ml-1 text-school-warning">
                        <span class="material-symbols-rounded text-[16px]">error</span>
                        <p class="text-xs font-medium">{{ $message }}</p>
                    </div>
                @enderror
            </div>

            <div class="flex flex-col gap-4 mt-8">
                <x-button as="button" type="submit" text="Přihlásit se"
                    icon="login" iconPosition="right" iconAnimation="forward"
                    fullWidth size="xl" />
            </div>
        </form>

        <form action="{{ route('auth.send-link') }}" method="POST" class="mt-8 border-t border-gray-200/60 pt-6">
            @csrf
            <input type="hidden" name="email" value="{{ $email }}">
            <p class="text-xs text-gray-400 mb-2">Zapomněli jste heslo?</p>
            <button type="submit"
                class="text-sm font-bold text-school-primary hover:text-school-hover hover:underline transition-colors bg-transparent border-none cursor-pointer w-full text-center">
                Odeslat odkaz na přihlášení do e-mailu
            </button>
        </form>

    </div>
@endsection
