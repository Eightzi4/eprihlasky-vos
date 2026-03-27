@php
    $programData = $program;
    $fieldValue = function (string $field, $default = null) use ($programData, $prefix) {
        return old($field, $programData->{$field} ?? $default);
    };
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    @foreach ([
        ['name' => 'name', 'label' => 'Název programu', 'icon' => 'school', 'required' => true],
        ['name' => 'code', 'label' => 'Kód programu', 'icon' => 'sell'],
        ['name' => 'degree', 'label' => 'Titul', 'icon' => 'workspace_premium', 'required' => true],
        ['name' => 'form', 'label' => 'Forma studia', 'icon' => 'view_day', 'required' => true],
        ['name' => 'length', 'label' => 'Délka studia', 'icon' => 'schedule', 'required' => true],
        ['name' => 'language', 'label' => 'Jazyk', 'icon' => 'translate', 'required' => true],
        ['name' => 'location', 'label' => 'Místo studia', 'icon' => 'location_on', 'required' => true],
        ['name' => 'tuition_fee', 'label' => 'Školné', 'icon' => 'payments'],
        ['name' => 'image_path', 'label' => 'URL obrázku', 'icon' => 'image', 'span' => 2],
    ] as $field)
        <div class="{{ ($field['span'] ?? 1) === 2 ? 'md:col-span-2' : '' }}">
            <label for="{{ $prefix }}-{{ $field['name'] }}"
                class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">{{ $field['label'] }}</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="material-symbols-rounded text-gray-400 text-[20px]">{{ $field['icon'] }}</span>
                </div>
                <input id="{{ $prefix }}-{{ $field['name'] }}" type="text" name="{{ $field['name'] }}"
                    value="{{ $fieldValue($field['name']) }}" {{ !empty($field['required']) ? 'required' : '' }}
                    class="w-full rounded-xl border border-gray-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-school-primary focus:border-school-primary bg-white/50 pl-10 py-3 text-sm placeholder-gray-400">
            </div>
            @if ($errorsBag->has($field['name']))
                <div class="flex items-center gap-1 mt-1.5 ml-1 text-school-warning">
                    <span class="material-symbols-rounded text-[16px]">error</span>
                    <p class="text-xs font-medium">{{ $errorsBag->first($field['name']) }}</p>
                </div>
            @endif
        </div>
    @endforeach

    <div class="md:col-span-2">
        <label for="{{ $prefix }}-description"
            class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Popis programu</label>
        <textarea id="{{ $prefix }}-description" name="description" rows="4"
            class="w-full rounded-xl border border-gray-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-school-primary focus:border-school-primary bg-white/50 px-4 py-3 text-sm placeholder-gray-400">{{ $fieldValue('description') }}</textarea>
        @if ($errorsBag->has('description'))
            <div class="flex items-center gap-1 mt-1.5 ml-1 text-school-warning">
                <span class="material-symbols-rounded text-[16px]">error</span>
                <p class="text-xs font-medium">{{ $errorsBag->first('description') }}</p>
            </div>
        @endif
    </div>
</div>

<label class="flex items-start gap-4 cursor-pointer group">
    <div class="relative flex items-center pt-1 flex-shrink-0">
        <input type="checkbox" name="is_active" value="1" {{ $fieldValue('is_active', true) ? 'checked' : '' }}
            class="peer h-6 w-6 cursor-pointer appearance-none rounded-md border-2 border-gray-300 bg-white transition-all checked:border-school-primary checked:bg-school-primary hover:border-school-primary">
        <span
            class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 mt-0.5 text-white opacity-0 peer-checked:opacity-100 pointer-events-none">
            <span class="material-symbols-rounded text-[18px] font-bold">check</span>
        </span>
    </div>
    <div class="text-sm text-gray-700 leading-relaxed">
        <span class="font-bold text-gray-900 block mb-1">Program je viditelný na webu</span>
        Pokud zůstane vypnutý, nebude se zobrazovat uchazečům mezi dostupnými studijními programy.
    </div>
</label>

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
