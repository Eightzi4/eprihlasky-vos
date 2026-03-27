@php
    $roundData = $round;
    $lockedStudyProgram = $lockedStudyProgram ?? null;
    $openModalTarget = $openModalTarget ?? null;
    $fieldValue = function (string $field, $default = null) use ($roundData) {
        return old($field, $roundData->{$field} ?? $default);
    };
    $dateValue = function (string $field) use ($roundData) {
        $old = old($field);
        if ($old !== null) {
            return $old;
        }
        return optional($roundData?->{$field})->format('Y-m-d\TH:i');
    };
@endphp

<div class="space-y-5">
    @if ($openModalTarget)
        <input type="hidden" name="open_modal_target" value="{{ $openModalTarget }}">
    @endif

    <div>
        <label for="{{ $prefix }}-study_program_id"
            class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Studijní program</label>
        @if ($lockedStudyProgram)
            <input type="hidden" name="study_program_id" value="{{ $lockedStudyProgram->id }}">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="material-symbols-rounded text-gray-400 text-[20px]">school</span>
                </div>
                <input id="{{ $prefix }}-study_program_id" type="text" value="{{ $lockedStudyProgram->name }}" readonly
                    class="w-full rounded-xl border border-gray-200 shadow-sm bg-gray-50/80 text-gray-500 pl-10 py-3 text-sm cursor-not-allowed">
            </div>
        @else
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="material-symbols-rounded text-gray-400 text-[20px]">school</span>
                </div>
                <select id="{{ $prefix }}-study_program_id" name="study_program_id" required
                    class="w-full rounded-xl border border-gray-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-school-primary focus:border-school-primary bg-white/50 pl-10 pr-4 py-3 text-sm appearance-none">
                    <option value="">Vyberte program</option>
                    @foreach ($programs as $programOption)
                        <option value="{{ $programOption->id }}"
                            {{ (string) $fieldValue('study_program_id') === (string) $programOption->id ? 'selected' : '' }}>
                            {{ $programOption->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif
        @if ($errorsBag->has('study_program_id'))
            <div class="flex items-center gap-1 mt-1.5 ml-1 text-school-warning">
                <span class="material-symbols-rounded text-[16px]">error</span>
                <p class="text-xs font-medium">{{ $errorsBag->first('study_program_id') }}</p>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        @foreach ([['name' => 'academic_year', 'label' => 'Akademický rok', 'icon' => 'calendar_month', 'required' => true], ['name' => 'label', 'label' => 'Označení kola', 'icon' => 'bookmark'], ['name' => 'opens_at', 'label' => 'Otevření', 'icon' => 'event_upcoming', 'type' => 'datetime-local', 'required' => true], ['name' => 'closes_at', 'label' => 'Uzavření', 'icon' => 'event_busy', 'type' => 'datetime-local', 'required' => true], ['name' => 'max_applicants', 'label' => 'Maximální počet uchazečů', 'icon' => 'groups', 'type' => 'number']] as $field)
            <div class="{{ $field['name'] === 'max_applicants' ? 'md:col-span-2' : '' }}">
                <label for="{{ $prefix }}-{{ $field['name'] }}"
                    class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">{{ $field['label'] }}</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="material-symbols-rounded text-gray-400 text-[20px]">{{ $field['icon'] }}</span>
                    </div>
                    <input id="{{ $prefix }}-{{ $field['name'] }}" type="{{ $field['type'] ?? 'text' }}"
                        name="{{ $field['name'] }}"
                        value="{{ ($field['type'] ?? null) === 'datetime-local' ? $dateValue($field['name']) : $fieldValue($field['name']) }}"
                        {{ !empty($field['required']) ? 'required' : '' }}
                        {{ ($field['type'] ?? null) === 'datetime-local' ? 'step=300' : '' }}
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
    </div>

    <p class="text-xs text-gray-400 leading-relaxed">
        Datum i čas zadávejte ve 24hodinovém formátu. Pole používá nativní datumový a časový picker pro rychlejší výběr.
    </p>
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
        <span class="font-bold text-gray-900 block mb-1">Kolo je viditelné a může se otevřít</span>
        Vypnuté kolo zůstane v administraci, ale uchazeči ho na webu neuvidí.
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
