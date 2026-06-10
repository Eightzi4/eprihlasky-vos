@extends('layouts.app')

@section('title', 'E-přihláška | OAUH')

@section('content')
    <div
        class="w-full max-w-2xl text-center bg-white/80 backdrop-blur-xl p-10 rounded-3xl shadow-2xl border border-white/60 ring-1 ring-black/5">

        <div
            class="inline-flex items-center px-3 py-1 rounded-full bg-[#f7f7f7] text-xs font-bold mb-8 border border-gray-200 shadow-sm">
            Příjímací řízení 2025/2026
        </div>

        <h1 class="text-4xl sm:text-5xl font-bold tracking-tight text-gray-900 mb-6 drop-shadow-sm">
            Elektronická přihláška<br>ke studiu na VOŠ
        </h1>

        <p class="text-lg text-gray-600 mb-12 max-w-lg mx-auto leading-relaxed font-medium">
            Vítejte v informačním systému pro uchazeče. Zde si můžete vybrat studijní program, vyplnit přihlášku a sledovat
            její stav.
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-6 w-full">

            <x-button as="a" href="{{ route('programs.index') }}"
                text="Podat novou přihlášku" icon="add" iconAnimation="rotate"
                size="xl" />

            <x-button as="a" href="{{ route('login') }}"
                text="Moje přihlášky" icon="grid_view"
                size="xl" />
        </div>

        <div class="mt-14 pt-8 border-t border-gray-200/60">
            <p class="text-sm text-gray-500 font-medium">
                Potřebujete poradit? Kontaktujte studijní oddělení VOŠ:
            </p>
            <div class="mt-2 flex flex-col sm:flex-row justify-center gap-2 sm:gap-6 text-base">
                <a href="mailto:info@oauh.cz"
                    class="text-school-primary hover:text-school-hover font-semibold transition-colors flex items-center justify-center gap-2">
                    <span class="material-symbols-rounded text-[18px]">mail</span>
                    info@oauh.cz
                </a>
                <a href="tel:+420572433012"
                    class="text-school-primary hover:text-school-hover font-semibold transition-colors flex items-center justify-center gap-2">
                    <span class="material-symbols-rounded text-[18px]">call</span>
                    +420 572 433 012
                </a>
            </div>
        </div>
    </div>
@endsection
