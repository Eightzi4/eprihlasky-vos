@php
    $fieldValue = fn(string $field, $default = null) => old($field, $adminModel->{$field} ?? $default);
@endphp

<div class="space-y-5">
    <div>
        <label for="{{ $prefix }}-name"
            class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Jméno administrátora</label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="material-symbols-rounded text-gray-400 text-[20px]">person</span>
            </div>
            <input id="{{ $prefix }}-name" type="text" name="name" value="{{ $fieldValue('name') }}" required
                class="w-full rounded-xl border border-gray-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-school-primary focus:border-school-primary bg-white/50 pl-10 py-3 text-sm placeholder-gray-400">
        </div>
        @if ($errorsBag->has('name'))
            <div class="flex items-center gap-1 mt-1.5 ml-1 text-school-warning">
                <span class="material-symbols-rounded text-[16px]">error</span>
                <p class="text-xs font-medium">{{ $errorsBag->first('name') }}</p>
            </div>
        @endif
    </div>

    <div>
        <label for="{{ $prefix }}-email"
            class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">E-mailová adresa</label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="material-symbols-rounded text-gray-400 text-[20px]">alternate_email</span>
            </div>
            <input id="{{ $prefix }}-email" type="email" name="email" value="{{ $fieldValue('email') }}" required
                class="w-full rounded-xl border border-gray-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-school-primary focus:border-school-primary bg-white/50 pl-10 py-3 text-sm placeholder-gray-400">
        </div>
        @if ($errorsBag->has('email'))
            <div class="flex items-center gap-1 mt-1.5 ml-1 text-school-warning">
                <span class="material-symbols-rounded text-[16px]">error</span>
                <p class="text-xs font-medium">{{ $errorsBag->first('email') }}</p>
            </div>
        @endif
    </div>

    <div>
        <label for="{{ $prefix }}-is_main_admin"
            class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Typ účtu</label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="material-symbols-rounded text-gray-400 text-[20px]">shield_person</span>
            </div>
            <select id="{{ $prefix }}-is_main_admin" name="is_main_admin"
                class="w-full rounded-xl border border-gray-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-school-primary focus:border-school-primary bg-white/50 pl-10 pr-4 py-3 text-sm appearance-none">
                <option value="0" {{ $fieldValue('is_main_admin', false) ? '' : 'selected' }}>Administrátor</option>
                <option value="1" {{ $fieldValue('is_main_admin', false) ? 'selected' : '' }}>Hlavní administrátor</option>
            </select>
        </div>
        @if ($errorsBag->has('is_main_admin'))
            <div class="flex items-center gap-1 mt-1.5 ml-1 text-school-warning">
                <span class="material-symbols-rounded text-[16px]">error</span>
                <p class="text-xs font-medium">{{ $errorsBag->first('is_main_admin') }}</p>
            </div>
        @endif
    </div>
</div>

<button type="submit"
    class="group relative w-full flex items-center justify-center px-8 py-4 rounded-xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-300 cursor-pointer">
    <div class="absolute inset-0 topo-bg opacity-50 transition-opacity duration-300"></div>
    <div
        class="absolute inset-0 bg-white/60 backdrop-blur-[2px] group-hover:backdrop-blur-[4px] transition-all duration-300">
    </div>
    <div class="absolute inset-0 rounded-xl border border-white/60 border-b-4 border-b-gray-200/50"></div>
    <span class="relative z-10 text-gray-900 font-bold text-base flex items-center drop-shadow-sm">
        <span
            class="material-symbols-rounded mr-2 text-[20px] text-gray-600 group-hover:text-school-primary transition-colors">save</span>
        {{ $submitLabel }}
    </span>
</button>
