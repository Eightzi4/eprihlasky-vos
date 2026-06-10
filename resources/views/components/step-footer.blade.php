@props([
    'application',
    'prevRoute' => null,
    'prevLabel' => 'Zpět',
    'nextRoute' => null,
    'nextLabel' => null,
    'submitLabel' => null,
    'submitDisabled' => false,
])

<div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-sm border border-white/60 p-4 ring-1 ring-black/5">
    <div class="flex justify-between items-center gap-4">

        @if ($prevRoute)
            <button type="button" onclick="navigateTo('{{ route($prevRoute, $application->id) }}')"
                class="group flex items-center justify-center px-4 py-2.5 sm:px-6 sm:py-3 rounded-xl transition-all duration-300 hover:bg-gray-100 cursor-pointer bg-transparent border-none">
                <span
                    class="text-gray-600 font-bold text-sm flex items-center group-hover:text-gray-900 transition-colors whitespace-nowrap">
                    <span
                        class="material-symbols-rounded mr-1 sm:mr-2 text-[18px] transition-transform duration-300 group-hover:-translate-x-1">arrow_back</span>
                    <span class="hidden sm:inline">{{ $prevLabel }}</span>
                    <span class="sm:hidden">Zpět</span>
                </span>
            </button>
        @else
            <button type="button" onclick="navigateTo('dashboard')"
                class="group flex items-center justify-center px-4 py-2.5 sm:px-6 sm:py-3 rounded-xl transition-all duration-300 hover:bg-gray-100 cursor-pointer bg-transparent border-none">
                <span
                    class="text-gray-500 font-bold text-sm group-hover:text-gray-800 transition-colors whitespace-nowrap">Zrušit</span>
            </button>
        @endif

        @if ($submitLabel)
            <div x-data="{ disabled: {{ $submitDisabled ? 'true' : 'false' }} }" x-init="window.addEventListener('status-updated', e => { disabled = !e.detail.canSubmit; })">

                <template x-if="disabled">
                    <div class="relative flex items-center justify-center px-6 py-3 sm:px-8 sm:py-4 rounded-xl overflow-hidden opacity-50 cursor-not-allowed select-none"
                        title="Vyplňte všechny povinné údaje a přijměte souhlas s GDPR">
                        <div class="absolute inset-0 bg-gray-100 rounded-xl border border-gray-200"></div>
                        <span
                            class="relative z-10 text-gray-500 font-bold text-base sm:text-lg flex items-center whitespace-nowrap">
                            {{ $submitLabel }}
                            <span class="material-symbols-rounded ml-2 sm:ml-3 text-[20px]">lock</span>
                        </span>
                    </div>
                </template>

                <template x-if="!disabled">
                    <x-button as="button" type="submit" size="xl">
                        {{ $submitLabel }}
                        <span class="material-symbols-rounded ml-2 sm:ml-3 text-[20px] text-school-primary transition-transform duration-300 group-hover/btn:translate-x-1">send</span>
                    </x-button>
                </template>
            </div>
        @elseif ($nextRoute)
            <x-button as="button" onclick="navigateTo('{{ route($nextRoute, $application->id) }}')"
                text="{{ $nextLabel ?? 'Pokračovat' }}" icon="arrow_forward"
                iconPosition="right" iconAnimation="forward" size="xl" />
        @endif

    </div>
</div>
