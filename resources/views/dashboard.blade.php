@extends('layouts.app')

@section('title', 'Nástěnka | VOŠ OAUH')

@section('header-right')
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit"
            class="group relative flex items-center justify-center px-4 py-2 rounded-xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-300 cursor-pointer">
            <div class="absolute inset-0 topo-bg opacity-50 transition-opacity duration-300"></div>
            <div
                class="absolute inset-0 bg-white/60 backdrop-blur-[2px] group-hover:backdrop-blur-[4px] transition-all duration-300">
            </div>
            <div class="absolute inset-0 rounded-xl border border-white/60 border-b-4 border-b-gray-200/50"></div>
            <span class="relative z-10 text-gray-600 font-bold text-xs flex items-center drop-shadow-sm">
                <span
                    class="material-symbols-rounded mr-2 text-[18px] text-gray-600 group-hover:text-school-primary transition-transform duration-300 group-hover:-translate-x-1">logout</span>
                Odhlásit se
            </span>
        </button>
    </form>
@endsection

@section('content')
    <div class="w-full max-w-5xl mx-auto">

        <div
            class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-sm border border-white/60 overflow-hidden ring-1 ring-black/5 mb-8">
            <div class="px-8 py-5 border-b border-gray-100/80 bg-white/40 flex items-center gap-3">
                <div class="h-8 w-8 rounded-lg bg-red-50 flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-rounded text-school-primary text-[20px]">manage_accounts</span>
                </div>
                <h2 class="font-bold text-gray-800 text-lg">Osobní údaje a nastavení</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2">
                <div class="p-8 border-b md:border-b-0 md:border-r border-gray-200/60 flex flex-col justify-between gap-5">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Kontaktní e-mail</p>
                        <p class="text-lg font-bold text-gray-900 break-all mb-1">{{ Auth::user()->email }}</p>
                        <p class="text-sm text-gray-500">Adresa, pomocí které se přihlašujete.</p>
                    </div>
                    <button onclick="openModal('email-modal')"
                        class="group relative flex items-center justify-center px-5 py-2.5 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 cursor-pointer self-start">
                        <div class="absolute inset-0 topo-bg opacity-40 transition-opacity duration-300"></div>
                        <div
                            class="absolute inset-0 bg-white/60 backdrop-blur-[2px] group-hover:backdrop-blur-[4px] transition-all duration-300">
                        </div>
                        <div class="absolute inset-0 rounded-xl border border-white/60 border-b-2 border-b-gray-200/50">
                        </div>
                        <span class="relative z-10 text-gray-700 font-bold text-sm flex items-center">
                            <span
                                class="material-symbols-rounded mr-2 text-[18px] text-gray-500 group-hover:text-school-primary transition-colors">edit</span>
                            Změnit e-mail
                        </span>
                    </button>
                </div>

                <div class="p-8 flex flex-col justify-between gap-5">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Heslo k účtu</p>
                        @if (Auth::user()->password)
                            <div class="flex items-center gap-2 text-green-600 font-bold text-lg">
                                <span class="material-symbols-rounded text-[20px]">check_circle</span>
                                Heslo je nastaveno
                            </div>
                        @else
                            <div class="flex items-center gap-2 text-orange-500 font-bold text-lg">
                                <span class="material-symbols-rounded text-[20px]">warning</span>
                                Heslo není nastaveno
                            </div>
                            <p class="text-sm text-gray-500 mt-1">Bez hesla se přihlašujete pouze odkazem z e-mailu.</p>
                        @endif
                    </div>
                    <button onclick="openModal('password-modal')"
                        class="group relative flex items-center justify-center px-5 py-2.5 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 cursor-pointer self-start">
                        <div class="absolute inset-0 topo-bg opacity-40 transition-opacity duration-300"></div>
                        <div
                            class="absolute inset-0 bg-white/60 backdrop-blur-[2px] group-hover:backdrop-blur-[4px] transition-all duration-300">
                        </div>
                        <div class="absolute inset-0 rounded-xl border border-white/60 border-b-2 border-b-gray-200/50">
                        </div>
                        <span class="relative z-10 text-gray-700 font-bold text-sm flex items-center">
                            <span
                                class="material-symbols-rounded mr-2 text-[18px] text-gray-500 group-hover:text-school-primary transition-colors">lock_reset</span>
                            {{ Auth::user()->password ? 'Změnit heslo' : 'Vytvořit heslo' }}
                        </span>
                    </button>
                </div>
            </div>
        </div>

        <div
            class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-sm border border-white/60 overflow-hidden ring-1 ring-black/5 mb-12">

            <div class="px-8 py-5 border-b border-gray-100/80 bg-white/40 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="h-8 w-8 rounded-lg bg-red-50 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-rounded text-school-primary text-[20px]">description</span>
                    </div>
                    <h2 class="font-bold text-gray-800 text-lg">Moje přihlášky</h2>
                </div>
                @if ($applications->isNotEmpty())
                    <a href="{{ route('programs.index') }}"
                        class="group relative flex items-center justify-center px-5 py-2.5 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300">
                        <div class="absolute inset-0 topo-bg opacity-40 transition-opacity duration-300"></div>
                        <div
                            class="absolute inset-0 bg-white/60 backdrop-blur-[2px] group-hover:backdrop-blur-[4px] transition-all duration-300">
                        </div>
                        <div class="absolute inset-0 rounded-xl border border-white/60 border-b-2 border-b-gray-200/50">
                        </div>
                        <span class="relative z-10 text-gray-900 font-bold text-sm flex items-center">
                            <span
                                class="material-symbols-rounded mr-2 text-[20px] text-gray-600 group-hover:text-school-primary transition-all duration-300 group-hover:rotate-90">add</span>
                            Nová přihláška
                        </span>
                    </a>
                @endif
            </div>

            @if ($applications->isEmpty())
                <div class="p-16 flex flex-col items-center justify-center text-center">
                    <p class="text-gray-500 text-lg mb-1">Zatím jste nepodal(a) žádnou přihlášku.</p>
                    <p class="text-gray-400 text-sm mb-10">Vyberte si obor z naší nabídky a vyplňte formulář.</p>
                    <a href="{{ route('programs.index') }}"
                        class="group relative flex items-center justify-center px-10 py-5 rounded-xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-300 cursor-pointer">
                        <div class="absolute inset-0 topo-bg opacity-50 transition-opacity duration-300"></div>
                        <div
                            class="absolute inset-0 bg-white/60 backdrop-blur-[2px] group-hover:backdrop-blur-[4px] transition-all duration-300">
                        </div>
                        <div class="absolute inset-0 rounded-xl border border-white/60 border-b-4 border-b-gray-200/50">
                        </div>
                        <span class="relative z-10 text-gray-900 font-bold text-xl flex items-center drop-shadow-sm">
                            <span
                                class="material-symbols-rounded mr-3 text-[24px] text-gray-600 group-hover:text-school-primary transition-all duration-300 group-hover:rotate-90">add</span>
                            Podat novou přihlášku
                        </span>
                    </a>
                </div>
            @else
                <div class="p-6 space-y-4">
                    @foreach ($applications as $app)
                        @php
                            $statuses = [
                                $app->step1Status(),
                                $app->isStep1Locked()
                                    ? 'locked'
                                    : ($app->identity_verified
                                        ? 'complete'
                                        : 'incomplete'),
                                $app->isStep1Locked() ? 'locked' : ($app->gdpr_accepted ? 'complete' : 'incomplete'),
                                $app->submitted ? 'complete' : 'incomplete',
                                $app->step2Status(),
                                $app->paymentStatus(),
                            ];

                            $total = count($statuses);
                            $completed = collect($statuses)
                                ->filter(fn($s) => $s === 'complete' || $s === 'locked')
                                ->count();
                            $hasPending = collect($statuses)->contains('pending');
                            $hasIssue = collect($statuses)->contains('incomplete');

                            $dotColor = fn($s) => match ($s) {
                                'complete' => 'bg-green-500',
                                'locked' => 'bg-gray-300',
                                'pending' => 'bg-blue-400',
                                default => 'bg-orange-400',
                            };

                            if ($app->submitted) {
                                $overallCls = 'bg-green-50 text-green-700 border-green-200';
                                $overallIcon = 'check_circle';
                                $overallText = 'Odesláno';
                            } elseif ($hasIssue) {
                                $overallCls = 'bg-amber-50 text-amber-700 border-amber-200';
                                $overallIcon = 'edit_note';
                                $overallText = 'Rozpracováno';
                            } else {
                                $overallCls = 'bg-blue-50 text-blue-700 border-blue-200';
                                $overallIcon = 'pending';
                                $overallText = 'Čeká na ověření';
                            }

                            $actionRoute = $app->submitted
                                ? route('application.step5', $app->id)
                                : route('application.step1', $app->id);
                            $actionText = $app->submitted ? 'Zobrazit přihlášku' : 'Dokončit přihlášku';
                            $actionIcon = $app->submitted ? 'visibility' : 'arrow_forward';
                        @endphp

                        <div
                            class="group relative bg-white/60 backdrop-blur-md rounded-2xl border border-white/60 ring-1 ring-black/5 overflow-hidden transition-all duration-300 hover:shadow-lg hover:bg-white/80 hover:ring-black/10">
                            <div class="flex flex-col sm:flex-row">

                                <div class="relative w-full sm:w-56 h-44 sm:h-auto flex-shrink-0 overflow-hidden">
                                    <img src="{{ $app->studyProgram->image_path }}" alt=""
                                        class="absolute inset-0 w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-500">
                                    <div
                                        class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent sm:bg-gradient-to-r sm:from-transparent sm:to-black/5">
                                    </div>
                                </div>

                                <div class="flex-grow p-7 flex flex-col sm:flex-row gap-6 min-w-0">

                                    <div class="flex-grow min-w-0 flex flex-col justify-between gap-4">
                                        <div>
                                            <h3
                                                class="text-xl font-bold text-gray-900 group-hover:text-school-primary transition-colors leading-tight mb-2">
                                                {{ $app->studyProgram->name }}
                                            </h3>
                                            <div
                                                class="flex flex-wrap items-center gap-x-4 gap-y-1.5 text-sm text-gray-400 font-medium">
                                                @if ($app->round)
                                                    <span class="flex items-center gap-1.5">
                                                        <span
                                                            class="material-symbols-rounded text-[16px]">calendar_month</span>
                                                        {{ $app->round->label ?? $app->round->academic_year }}
                                                    </span>
                                                @endif
                                                @if ($app->application_number)
                                                    <span class="flex items-center gap-1.5 text-school-primary font-bold">
                                                        <span class="material-symbols-rounded text-[16px]">tag</span>
                                                        {{ $app->application_number }}
                                                    </span>
                                                @endif
                                                <span class="flex items-center gap-1.5">
                                                    <span class="material-symbols-rounded text-[16px]">schedule</span>
                                                    {{ $app->created_at->format('j. n. Y') }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-4">
                                            <div class="flex items-center gap-1.5">
                                                @foreach ($statuses as $s)
                                                    <span class="h-2.5 w-2.5 rounded-full {{ $dotColor($s) }}"></span>
                                                @endforeach
                                            </div>
                                            <span class="text-sm font-semibold text-gray-500">
                                                Splněno {{ $completed }}/{{ $total }}
                                            </span>
                                            @if ($hasPending)
                                                <span class="text-sm font-semibold text-blue-500 flex items-center gap-1">
                                                    <span class="material-symbols-rounded text-[15px]">pending</span>
                                                    Čeká na schválení
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div
                                        class="flex sm:flex-col items-center sm:items-end justify-between sm:justify-between gap-4 sm:flex-shrink-0">
                                        <span
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold border {{ $overallCls }} flex-shrink-0">
                                            <span class="material-symbols-rounded text-[15px]">{{ $overallIcon }}</span>
                                            {{ $overallText }}
                                        </span>

                                        <a href="{{ $actionRoute }}"
                                            class="group/btn relative flex items-center justify-center px-5 py-2.5 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 cursor-pointer flex-shrink-0">
                                            <div
                                                class="absolute inset-0 topo-bg opacity-30 transition-opacity duration-300">
                                            </div>
                                            <div
                                                class="absolute inset-0 bg-white/60 backdrop-blur-[2px] group-hover/btn:backdrop-blur-[4px] transition-all duration-300">
                                            </div>
                                            <div
                                                class="absolute inset-0 rounded-xl border border-white/60 border-b-2 border-b-gray-200/50">
                                            </div>
                                            <span
                                                class="relative z-10 text-gray-900 font-bold text-sm flex items-center whitespace-nowrap">
                                                <span
                                                    class="material-symbols-rounded mr-1.5 text-[17px] text-gray-500 group-hover/btn:text-school-primary transition-colors duration-300">{{ $actionIcon }}</span>
                                                {{ $actionText }}
                                            </span>
                                        </a>
                                    </div>

                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="border-t border-gray-200/60 px-8 py-6 bg-gray-50/30 text-center">
                <p class="text-sm text-gray-500 font-medium mb-3">Máte problém s účtem nebo přihláškou? Kontaktujte studijní
                    oddělení.</p>
                <div class="flex flex-col sm:flex-row justify-center gap-2 sm:gap-6 text-sm">
                    <a href="mailto:info@oauh.cz"
                        class="text-school-primary hover:text-school-hover font-semibold transition-colors flex items-center justify-center gap-2">
                        <span class="material-symbols-rounded text-[16px]">mail</span>
                        info@oauh.cz
                    </a>
                    <a href="tel:+420572433012"
                        class="text-school-primary hover:text-school-hover font-semibold transition-colors flex items-center justify-center gap-2">
                        <span class="material-symbols-rounded text-[16px]">call</span>
                        +420 572 433 012
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div id="email-modal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm" onclick="closeModal('email-modal')"></div>
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div
                class="bg-white/90 backdrop-blur-xl rounded-3xl shadow-2xl w-full max-w-md p-8 relative border border-white/60 ring-1 ring-black/5">
                <button type="button" onclick="closeModal('email-modal')"
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                    <span class="material-symbols-rounded text-[24px]">close</span>
                </button>
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Změna e-mailu</h2>
                <form action="{{ route('profile.email') }}" method="POST">
                    @csrf
                    <div class="mb-6">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Nový
                            e-mail</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-symbols-rounded text-gray-400 text-[20px]">alternate_email</span>
                            </div>
                            <input type="email" name="email" required value="{{ Auth::user()->email }}"
                                class="w-full rounded-xl border border-gray-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-school-primary focus:border-school-primary bg-white/50 pl-10 py-3 transition-all placeholder-gray-400 text-sm">
                        </div>
                        @error('email')
                            <p class="text-school-warning text-xs mt-2 ml-1 flex items-center gap-1">
                                <span class="material-symbols-rounded text-[14px]">error</span>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                    <button type="submit"
                        class="group relative w-full flex items-center justify-center px-8 py-4 rounded-xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-300 cursor-pointer">
                        <div class="absolute inset-0 topo-bg opacity-50 transition-opacity duration-300"></div>
                        <div
                            class="absolute inset-0 bg-white/60 backdrop-blur-[2px] group-hover:backdrop-blur-[4px] transition-all duration-300">
                        </div>
                        <div class="absolute inset-0 rounded-xl border border-white/60 border-b-4 border-b-gray-200/50">
                        </div>
                        <span class="relative z-10 text-gray-900 font-bold text-base flex items-center drop-shadow-sm">
                            <span
                                class="material-symbols-rounded mr-2 text-[20px] text-gray-600 group-hover:text-school-primary transition-colors duration-300">save</span>
                            Uložit změny
                        </span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div id="password-modal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm" onclick="closeModal('password-modal')"></div>
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div
                class="bg-white/90 backdrop-blur-xl rounded-3xl shadow-2xl w-full max-w-md p-8 relative border border-white/60 ring-1 ring-black/5">
                <button type="button" onclick="closeModal('password-modal')"
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                    <span class="material-symbols-rounded text-[24px]">close</span>
                </button>
                <h2 class="text-2xl font-bold text-gray-900 mb-6">
                    {{ Auth::user()->password ? 'Změna hesla' : 'Nastavení hesla' }}
                </h2>
                <form action="{{ route('profile.password') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Nové
                            heslo</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-symbols-rounded text-gray-400 text-[20px]">lock</span>
                            </div>
                            <input type="password" name="password" required minlength="8"
                                placeholder="Minimálně 8 znaků"
                                class="w-full rounded-xl border border-gray-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-school-primary focus:border-school-primary bg-white/50 pl-10 py-3 transition-all placeholder-gray-400 text-sm">
                        </div>
                        @error('password')
                            <p class="text-school-warning text-xs mt-2 ml-1 flex items-center gap-1">
                                <span class="material-symbols-rounded text-[14px]">error</span>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                    <div class="mb-6">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Potvrzení
                            hesla</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-symbols-rounded text-gray-400 text-[20px]">lock_reset</span>
                            </div>
                            <input type="password" name="password_confirmation" required minlength="8"
                                placeholder="Zadejte heslo znovu"
                                class="w-full rounded-xl border border-gray-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-school-primary focus:border-school-primary bg-white/50 pl-10 py-3 transition-all placeholder-gray-400 text-sm">
                        </div>
                    </div>
                    <button type="submit"
                        class="group relative w-full flex items-center justify-center px-8 py-4 rounded-xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-300 cursor-pointer">
                        <div class="absolute inset-0 topo-bg opacity-50 transition-opacity duration-300"></div>
                        <div
                            class="absolute inset-0 bg-white/60 backdrop-blur-[2px] group-hover:backdrop-blur-[4px] transition-all duration-300">
                        </div>
                        <div class="absolute inset-0 rounded-xl border border-white/60 border-b-4 border-b-gray-200/50">
                        </div>
                        <span class="relative z-10 text-gray-900 font-bold text-base flex items-center drop-shadow-sm">
                            <span
                                class="material-symbols-rounded mr-2 text-[20px] text-gray-600 group-hover:text-school-primary transition-colors duration-300">save</span>
                            Uložit heslo
                        </span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }

        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }

        @if ($errors->has('email'))
            openModal('email-modal');
        @endif
        @if ($errors->has('password'))
            openModal('password-modal');
        @endif
    </script>
@endsection
