@props(['name', 'label', 'icon', 'value', 'placeholder' => '', 'verified' => false])
<div>
    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">
        {{ $label }}
    </label>
    <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <span class="material-symbols-rounded text-gray-400 text-[20px]">{{ $icon }}</span>
        </div>
        <input type="text" name="{{ $name }}" value="{{ $value }}" placeholder="{{ $placeholder }}"
            @if ($verified) readonly @endif
            :class="{
                'border-school-warning ring-1 ring-school-warning/30': fieldHasError('{{ $name }}'),
                'border-gray-200': !fieldHasError('{{ $name }}')
            }"
            class="block w-full pl-10 pr-3 py-3 border rounded-xl leading-5 sm:text-sm transition-all shadow-sm focus:outline-none
               @if ($verified) bg-gray-50 text-gray-500 cursor-not-allowed focus:border-gray-200
               @else
                   bg-white/50 text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-school-primary focus:border-school-primary @endif">

        @if ($verified)
            <p class="text-blue-600 text-xs mt-1.5 ml-1 font-bold flex items-center gap-1 absolute -bottom-6 left-0">
                <span class="material-symbols-rounded text-[14px]">verified</span> Ověřeno pomocí Identity občana
            </p>
        @endif
    </div>

    @error($name)
        <template x-if="showServerError('{{ $name }}')">
            <div data-field-error class="flex items-center gap-1 mt-1.5 ml-1 text-school-warning">
                <span class="material-symbols-rounded text-[16px]">error</span>
                <p class="text-xs font-medium">{{ $message }}</p>
            </div>
        </template>
    @enderror

    @if (!$verified)
        <template x-if="hasError('{{ $name }}')">
            <div data-field-error class="flex items-center gap-1 mt-1.5 ml-1 text-school-warning">
                <span class="material-symbols-rounded text-[16px]">error</span>
                <p class="text-xs font-medium" x-text="errors['{{ $name }}']"></p>
            </div>
        </template>
    @endif

    <div class="h-4"></div>
</div>
