@extends('layouts.app')

@section('title', 'Zkontrolujte e-mail | OAUH Administrace')

@section('header-left')
    <x-button as="a" href="{{ route('admin.login') }}" text="Zpět"
        icon="arrow_back" iconAnimation="back" size="sm" spanClass="text-gray-600" />
@endsection

@section('content')
    <div
        class="w-full max-w-lg text-center bg-white/80 backdrop-blur-xl p-10 rounded-3xl shadow-2xl border border-white/60 ring-1 ring-black/5">

        <h1 class="text-3xl font-bold text-gray-900 mb-6">Zkontrolujte svou schránku</h1>

        <div class="mb-10">
            <p class="text-gray-600 text-base mb-2">Přihlašovací odkaz jsme odeslali na adresu:</p>
            <div class="text-2xl font-bold text-school-primary break-all">{{ $email }}</div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-10 text-left">
            <div class="flex items-start gap-3">
                <div class="mt-0.5 p-1.5 bg-gray-100 rounded-lg">
                    <span class="material-symbols-rounded text-gray-500 text-[20px]">schedule</span>
                </div>
                <div>
                    <span class="block text-sm font-bold text-gray-900">Platnost odkazu</span>
                    <span class="text-xs text-gray-500">Odkaz vyprší za 30 minut.</span>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <div class="mt-0.5 p-1.5 bg-gray-100 rounded-lg">
                    <span class="material-symbols-rounded text-gray-500 text-[20px]">mark_email_unread</span>
                </div>
                <div>
                    <span class="block text-sm font-bold text-gray-900">Nenašli jste e-mail?</span>
                    <span class="text-xs text-gray-500">Zkontrolujte složku SPAM.</span>
                </div>
            </div>
        </div>

        <p class="text-xs text-gray-400">
            Udělali jste chybu v e-mailu?
            <a href="{{ route('admin.login') }}"
                class="text-school-primary hover:text-school-hover font-bold hover:underline transition-colors">Zkuste to
                znovu</a>.
        </p>
    </div>
@endsection
