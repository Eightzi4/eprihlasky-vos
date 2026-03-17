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
            @if ($submitDisabled)
                <div class="relative flex items-center justify-center px-6 py-3 sm:px-8 sm:py-4 rounded-xl overflow-hidden opacity-50 cursor-not-allowed select-none"
                    title="Vyplňte všechny povinné údaje a přijměte souhlas s GDPR">
                    <div class="absolute inset-0 bg-gray-100 rounded-xl border border-gray-200"></div>
                    <span
                        class="relative z-10 text-gray-500 font-bold text-base sm:text-lg flex items-center whitespace-nowrap">
                        {{ $submitLabel }}
                        <span class="material-symbols-rounded ml-2 sm:ml-3 text-[20px]">lock</span>
                    </span>
                </div>
            @else
                <button type="submit"
                    class="group relative flex items-center justify-center px-6 py-3 sm:px-8 sm:py-4 rounded-xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-300 cursor-pointer">
                    <div class="absolute inset-0 topo-bg opacity-50 transition-opacity duration-300"></div>
                    <div
                        class="absolute inset-0 bg-white/60 backdrop-blur-[2px] group-hover:backdrop-blur-[4px] transition-all duration-300">
                    </div>
                    <div class="absolute inset-0 rounded-xl border border-white/60 border-b-4 border-b-gray-200/50">
                    </div>
                    <span
                        class="relative z-10 text-gray-900 font-bold text-base sm:text-lg flex items-center drop-shadow-sm whitespace-nowrap">
                        {{ $submitLabel }}
                        <span
                            class="material-symbols-rounded ml-2 sm:ml-3 text-[20px] text-school-primary transition-transform duration-300 group-hover:translate-x-1">send</span>
                    </span>
                </button>
            @endif
        @elseif ($nextRoute)
            <button type="button" onclick="navigateTo('{{ route($nextRoute, $application->id) }}')"
                class="group relative flex items-center justify-center px-6 py-3 sm:px-8 sm:py-4 rounded-xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-300 cursor-pointer">
                <div class="absolute inset-0 topo-bg opacity-50"></div>
                <div
                    class="absolute inset-0 bg-white/60 backdrop-blur-[2px] group-hover:backdrop-blur-[4px] transition-all duration-300">
                </div>
                <div class="absolute inset-0 rounded-xl border border-white/60 border-b-4 border-b-gray-200/50"></div>
                <span
                    class="relative z-10 text-gray-900 font-bold text-base sm:text-lg flex items-center drop-shadow-sm whitespace-nowrap">
                    {{ $nextLabel ?? 'Pokračovat' }}
                    <span
                        class="material-symbols-rounded ml-2 text-[20px] text-gray-600 group-hover:text-school-primary transition-transform duration-300 group-hover:translate-x-1">arrow_forward</span>
                </span>
            </button>
        @endif

    </div>
</div>
