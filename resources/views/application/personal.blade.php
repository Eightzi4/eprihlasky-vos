@extends('layouts.application')

@section('form-content')
    @php
        $serverErrors = array_keys($errors->toArray());
        $serverMessages = collect($errors->toArray())->map(fn($msgs) => $msgs[0])->toArray();
        $vf = $application->verified_fields ?? [];
        $isLocked = $application->isStepLocked(1);
    @endphp

    <div x-data="stepValidator({
        step: 1,
        serverErrorFields: @json($serverErrors),
        serverMessages: @json($serverMessages),
        fields: [
            { name: 'first_name', message: 'Křestní jméno je povinné.' },
            { name: 'last_name', message: 'Příjmení je povinné.' },
            { name: 'gender', message: 'Pohlaví je povinné.' },
            { name: 'birth_number', message: 'Rodné číslo je povinné.' },
            { name: 'birth_date', message: 'Datum narození je povinné.' },
            { name: 'birth_city', message: 'Místo narození je povinné.' },
            { name: 'citizenship', message: 'Státní občanství je povinné.' },
            { name: 'email', message: 'E-mail je povinný.' },
            { name: 'phone', message: 'Telefon je povinný.' },
            { name: 'street', message: 'Ulice a číslo popisné jsou povinné.' },
            { name: 'city', message: 'Město je povinné.' },
            { name: 'zip', message: 'PSČ je povinné.' },
            { name: 'country', message: 'Stát je povinný.' },
        ]
    })">

        <div
            class="relative bg-white/80 backdrop-blur-xl rounded-3xl shadow-lg border-2 {{ $isLocked ? 'border-gray-200' : 'border-school-primary/20 hover:border-school-primary/40' }} p-6 sm:p-8 mb-8 overflow-hidden group transition-all duration-500">
            <div
                class="absolute -right-4 -bottom-12 opacity-[0.04] rotate-[15deg] pointer-events-none transition-transform duration-700 ease-out group-hover:rotate-[5deg] group-hover:scale-105">
                <span
                    class="material-symbols-rounded text-[200px] sm:text-[250px] {{ $isLocked ? 'text-gray-400' : 'text-school-primary' }} leading-none">
                    {{ $isLocked ? 'lock' : 'verified_user' }}
                </span>
            </div>
            <div class="relative z-10 flex flex-col xl:flex-row items-center justify-between gap-6">
                <div class="max-w-xl text-center xl:text-left">
                    <h3
                        class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2 tracking-tight flex items-center justify-center xl:justify-start gap-3">
                        <span
                            class="material-symbols-rounded {{ $isLocked ? 'text-gray-400' : 'text-school-primary' }} text-[28px] sm:text-[32px]">
                            {{ $isLocked ? 'lock' : 'fingerprint' }}
                        </span>
                        {{ $isLocked ? 'Sekce uzamčena' : 'Identita občana' }}
                    </h3>
                    <p class="text-gray-600 font-medium leading-relaxed text-base sm:text-lg">
                        @if ($isLocked)
                            Osobní údaje byly uzamčeny po odeslání přihlášky. Kontaktujte školu pro případné úpravy.
                        @else
                            Urychlete vyplňování načtením ověřených údajů přímo ze státních registrů.
                        @endif
                    </p>
                </div>
                @if (!$isLocked)
                    <div class="flex flex-col sm:flex-row xl:flex-col gap-3 w-full xl:w-auto">
                        <a href="{{ route('nia.real.login', $application->id) }}"
                            class="group/btn relative flex items-center justify-center px-6 py-3 sm:px-8 sm:py-4 rounded-xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-300 cursor-pointer w-full sm:w-auto xl:w-[280px]">
                            <div class="absolute inset-0 topo-bg opacity-50"></div>
                            <div
                                class="absolute inset-0 bg-white/60 backdrop-blur-[2px] group-hover/btn:backdrop-blur-[4px] transition-all duration-300">
                            </div>
                            <div class="absolute inset-0 rounded-xl border border-white/60 border-b-4 border-b-gray-200/50">
                            </div>
                            <span
                                class="relative z-10 text-gray-900 font-bold text-base sm:text-lg flex items-center drop-shadow-sm whitespace-nowrap">
                                Načíst přes NIA
                                <span
                                    class="material-symbols-rounded ml-2 text-[22px] text-gray-600 group-hover/btn:text-school-primary transition-transform duration-300 group-hover/btn:translate-x-1">login</span>
                            </span>
                        </a>
                        <a href="https://www.youtube.com/watch?v=ztrRu-olFy8" target="_blank"
                            class="group/btn relative flex items-center justify-center px-6 py-3 sm:px-8 sm:py-4 rounded-xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-300 cursor-pointer w-full sm:w-auto xl:w-[280px]">
                            <div class="absolute inset-0 topo-bg opacity-30"></div>
                            <div
                                class="absolute inset-0 bg-white/40 backdrop-blur-[2px] group-hover/btn:backdrop-blur-[4px] transition-all duration-300">
                            </div>
                            <div class="absolute inset-0 rounded-xl border border-white/60 border-b-4 border-b-gray-200/50">
                            </div>
                            <span
                                class="relative z-10 text-gray-700 font-bold text-base sm:text-lg flex items-center drop-shadow-sm whitespace-nowrap">
                                Video návod
                                <span
                                    class="material-symbols-rounded ml-2 text-[22px] text-red-600 group-hover/btn:scale-110 transition-transform duration-300">play_circle</span>
                            </span>
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <x-form-section title="Identifikační údaje">
            <x-form-field name="first_name" label="Křestní jméno" icon="person" :value="old('first_name', $application->first_name)" placeholder="Jan"
                :verified="in_array('first_name', $vf)" :locked="true" />
            <x-form-field name="last_name" label="Příjmení" icon="badge" :value="old('last_name', $application->last_name)" placeholder="Novák"
                :verified="in_array('last_name', $vf)" :locked="true" />
            <x-form-field name="gender" label="Pohlaví" icon="wc" type="select" :span="2" :options="['' => 'Vyberte pohlaví', 'Muž' => 'Muž', 'Žena' => 'Žena']"
                :value="old('gender', $application->gender)" :locked="$isLocked" />
        </x-form-section>

        <x-form-section title="Narození a občanství">
            <x-form-field name="birth_date" label="Datum narození" icon="calendar_today" type="date" :value="old('birth_date', $application->birth_date?->format('Y-m-d') ?? '')"
                :verified="in_array('birth_date', $vf)" :locked="true" />
            <x-form-field name="birth_number" label="Rodné číslo" icon="fingerprint" :value="old('birth_number', $application->birth_number)"
                placeholder="000101/1234" :verified="in_array('birth_number', $vf)" :locked="$isLocked" />
            <x-form-field name="citizenship" label="Státní občanství" icon="flag" type="select" :options="[
                '' => 'Vyberte státní občanství',
                'Česká republika' => 'Česká republika',
                'Slovensko' => 'Slovensko',
                'Jiné' => 'Jiné',
            ]"
                :value="old('citizenship', $application->citizenship)" :locked="$isLocked" />
            <x-form-field name="birth_city" label="Místo narození" icon="location_on" :value="old('birth_city', $application->birth_city)"
                placeholder="Uherské Hradiště" :verified="in_array('birth_city', $vf)" :locked="$isLocked" />
        </x-form-section>

        <x-form-section title="Adresa trvalého bydliště">
            <x-form-field name="country" label="Stát" icon="public" type="select" :options="[
                '' => 'Vyberte stát',
                'Česká republika' => 'Česká republika',
                'Slovensko' => 'Slovensko',
                'Jiné' => 'Jiné',
            ]" :value="old('country', $application->country)"
                :locked="$isLocked" />
            <x-form-field name="city" label="Obec / Město" icon="location_city" :value="old('city', $application->city)"
                placeholder="Uherské Hradiště" :verified="in_array('city', $vf)" :locked="true" />
            <x-form-field name="street" label="Ulice a číslo popisné" icon="home" :value="old('street', $application->street)"
                placeholder="Masarykovo náměstí 123" :verified="in_array('street', $vf)" :locked="true" />
            <x-form-field name="zip" label="PSČ" icon="markunread_mailbox" :value="old('zip', $application->zip)" placeholder="686 01"
                :verified="in_array('zip', $vf)" :locked="true" />
        </x-form-section>

        <x-form-section title="Kontaktní údaje">
            <x-form-field name="email" label="E-mail" icon="alternate_email" type="email" :value="old('email', $application->email ?? Auth::user()->email)"
                placeholder="jan.novak@example.com" :locked="$isLocked" />
            <x-form-field name="phone" label="Telefon" icon="call" :value="old('phone', $application->phone)" placeholder="+420 777 123 456"
                :locked="$isLocked" />
        </x-form-section>

        <x-step-footer :application="$application" next-route="application.step2" />

    </div>
@endsection
