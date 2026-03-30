@props([
    'name',
    'label',
    'icon' => null,
    'type' => 'text',
    'value' => '',
    'placeholder' => '',
    'options' => [],
    'verified' => false,
    'locked' => false,
    'niaRequired' => false,
    'rows' => 3,
    'accept' => '.pdf,.jpg,.jpeg,.png',
    'span' => 1,
])

@php
    $isLocked = $verified || $locked;
    $hasValue = filled($value);
    $needsNia = $niaRequired && !$verified && !$hasValue;
    $showVerified = $verified && $hasValue;
    $showErrors = !$isLocked;
    $spanClass = $span === 2 ? 'sm:col-span-2' : '';

    $baseInput = 'block w-full border rounded-xl leading-5 sm:text-sm transition-all shadow-sm focus:outline-none';
    $lockedCls = 'bg-gray-50 text-gray-500 cursor-not-allowed focus:border-gray-200 border-gray-200';
    $editableCls =
        'border-gray-200 bg-white/50 text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-school-primary focus:border-school-primary';
    $paddingCls = $icon ? 'pl-10 pr-3 py-3' : 'px-4 py-3';

    $inputStateCls = $isLocked ? $lockedCls : $editableCls;
@endphp

<div class="{{ $spanClass }}">
    @if ($label)
        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">{{ $label }}</label>
    @endif

    <div class="relative">
        @if ($icon)
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="material-symbols-rounded text-gray-400 text-[20px]">{{ $icon }}</span>
            </div>
        @endif

        @if ($type === 'select')
            <select name="{{ $name }}" @if ($isLocked) disabled @endif
                @if (!$isLocked) data-autosave="{{ $name }}" @endif
                :class="{
                    'border-school-warning ring-1 ring-school-warning/30': fieldHasError('{{ $name }}') && !
                        {{ $isLocked ? 'true' : 'false' }},
                    'border-gray-200': !fieldHasError('{{ $name }}') || {{ $isLocked ? 'true' : 'false' }}
                }"
                class="{{ $baseInput }} appearance-none {{ $paddingCls }} pr-10 {{ $inputStateCls }}">
                @foreach ($options as $optVal => $optLabel)
                    <option value="{{ $optVal }}" {{ (string) $value === (string) $optVal ? 'selected' : '' }}>
                        {{ $optLabel }}
                    </option>
                @endforeach
            </select>
            <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                <span class="material-symbols-rounded text-gray-500">expand_more</span>
            </div>
        @elseif ($type === 'textarea')
            <textarea name="{{ $name }}" rows="{{ $rows }}" placeholder="{{ $placeholder }}"
                @if ($isLocked) readonly @endif
                @if (!$isLocked) data-autosave="{{ $name }}" @endif
                :class="{
                    'border-school-warning ring-1 ring-school-warning/30': fieldHasError('{{ $name }}') && !
                        {{ $isLocked ? 'true' : 'false' }},
                    'border-gray-200': !fieldHasError('{{ $name }}') || {{ $isLocked ? 'true' : 'false' }}
                }"
                class="{{ $baseInput }} appearance-none {{ $paddingCls }} min-h-[120px] resize-none {{ $inputStateCls }}">{{ $value }}</textarea>
        @elseif ($type === 'file')
            @php
                $isMultiple = str_ends_with($name, '[]');
                $uploadIcon = $isMultiple ? 'library_add' : 'cloud_upload';
                $uploadPrompt = $isMultiple
                    ? 'Klikněte pro přidání souborů nebo je přetáhněte sem'
                    : 'Klikněte pro výběr souboru nebo jej přetáhněte sem';
            @endphp
            <div x-data="fileUploader({
                uploadUrl: FILE_UPLOAD_URL,
                csrfToken: CSRF_TOKEN,
                fieldName: '{{ $name }}',
                multiple: {{ $isMultiple ? 'true' : 'false' }},
            })" class="space-y-3">

                <div class="relative group cursor-pointer transition-all duration-300"
                    x-bind:class="{
                        'bg-red-50/50 border-school-primary ring-2 ring-school-primary/20': isDragging || isUploading,
                        'hover:border-school-primary hover:bg-red-50/30': !isDragging && !isUploading
                    }"
                    @dragover.prevent="isDragging = true" @dragleave.prevent="isDragging = false"
                    @drop.prevent="isDragging = false; handleFiles($event.dataTransfer.files)"
                    @click="$refs.fileInput.click()">

                    <input type="file" x-ref="fileInput" class="hidden" accept="{{ $accept }}"
                        @if ($isMultiple) multiple @endif @change="handleFiles($event.target.files)">

                    <div class="border-2 border-dashed border-gray-300 rounded-2xl p-8 text-center flex flex-col items-center justify-center transition-colors"
                        x-bind:class="{ 'border-transparent': isDragging || isUploading }">
                        <div class="h-12 w-12 bg-gray-100 rounded-full flex items-center justify-center mb-3 text-gray-400 transition-colors"
                            x-bind:class="{ 'bg-white text-school-primary': isDragging || isUploading }">
                            <span class="material-symbols-rounded text-[24px]"
                                x-show="!isUploading">{{ $uploadIcon }}</span>
                            <span class="material-symbols-rounded text-[24px] animate-spin"
                                x-show="isUploading">progress_activity</span>
                        </div>
                        <p class="text-sm font-bold text-gray-700 transition-colors"
                            x-bind:class="{ 'text-school-primary': isDragging || isUploading }">
                            <span x-show="!isDragging && !isUploading">{{ $uploadPrompt }}</span>
                            <span x-show="isDragging">Pusťte soubory zde</span>
                            <span x-show="isUploading">Nahrávám…</span>
                        </p>
                        <p class="text-xs text-gray-400 mt-1">PDF, JPG, PNG (Max 10 MB)</p>
                    </div>
                </div>

                <div x-show="uploadError" class="flex items-center gap-1 mt-1.5 ml-1 text-school-warning">
                    <span class="material-symbols-rounded text-[16px]">error</span>
                    <p class="text-xs font-medium" x-text="uploadError"></p>
                </div>

                <div class="space-y-2">
                    <template x-for="file in uploadedFiles" :key="file.id">
                        <div
                            class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded-xl shadow-sm">
                            <div class="flex items-center gap-3 overflow-hidden">
                                <template x-if="file.previewUrl">
                                    <div
                                        class="h-10 w-10 rounded-lg overflow-hidden border border-gray-100 flex-shrink-0">
                                        <img :src="file.previewUrl" class="w-full h-full object-cover">
                                    </div>
                                </template>
                                <template x-if="!file.previewUrl">
                                    <div
                                        class="h-10 w-10 bg-green-50 rounded-lg flex items-center justify-center text-green-600 border border-green-100 flex-shrink-0">
                                        <span class="material-symbols-rounded text-[20px]"
                                            x-text="getIcon(file.type)"></span>
                                    </div>
                                </template>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-gray-900 truncate" x-text="file.name"></p>
                                    <p class="text-xs text-green-600">Uloženo &bull; <span x-text="file.size"></span>
                                    </p>
                                </div>
                            </div>
                            <button type="button" @click="deleteFile(file.attachmentId)"
                                class="text-gray-400 hover:text-red-500 transition-colors p-2">
                                <span class="material-symbols-rounded text-[20px]">delete</span>
                            </button>
                        </div>
                    </template>

                    {{ $slot }}
                </div>
            </div>
        @else
            <input type="{{ $type }}" name="{{ $name }}" value="{{ $value }}"
                placeholder="{{ $placeholder }}" @if ($isLocked) readonly @endif
                @if (!$isLocked) data-autosave="{{ $name }}" @endif
                :class="{
                    'border-school-warning ring-1 ring-school-warning/30': fieldHasError('{{ $name }}') && !
                        {{ $isLocked ? 'true' : 'false' }},
                    'border-gray-200': !fieldHasError('{{ $name }}') || {{ $isLocked ? 'true' : 'false' }}
                }"
                class="{{ $baseInput }} {{ $paddingCls }} {{ $inputStateCls }}">
        @endif
    </div>

    @if ($showVerified)
        <p class="text-blue-600 text-xs mt-1.5 ml-1 font-bold flex items-center gap-1">
            <span class="material-symbols-rounded text-[14px]">verified</span> Ověřeno pomocí Identity občana
        </p>
    @elseif ($needsNia)
        <div class="flex items-center gap-1 mt-1.5 ml-1 text-school-warning">
            <span class="material-symbols-rounded text-[16px]">error</span>
            <p class="text-xs font-medium">Nutno vyplnit pomocí Identity občana</p>
        </div>
    @elseif ($showErrors)
        @error($name)
            <template x-if="showServerError('{{ $name }}')">
                <div class="flex items-center gap-1 mt-1.5 ml-1 text-school-warning">
                    <span class="material-symbols-rounded text-[16px]">error</span>
                    <p class="text-xs font-medium">{{ $message }}</p>
                </div>
            </template>
        @enderror
        <template x-if="hasError('{{ $name }}')">
            <div class="flex items-center gap-1 mt-1.5 ml-1 text-school-warning">
                <span class="material-symbols-rounded text-[16px]">error</span>
                <p class="text-xs font-medium" x-text="errors['{{ $name }}']"></p>
            </div>
        </template>
    @endif

    <div class="h-4"></div>
</div>
