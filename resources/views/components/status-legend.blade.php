<div x-show="statusLegendOpen" x-transition.opacity
    class="fixed inset-0 z-[70] flex items-center justify-center px-4 py-6" style="display: none;">
    <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm" @click="statusLegendOpen = false"></div>
    <div
        class="relative w-full max-w-lg bg-white/95 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/60 ring-1 ring-black/5 overflow-hidden">
        <div class="flex items-start justify-between gap-4 px-6 py-5 border-b border-gray-100">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Vysvětlení stavů</h3>
                <p class="text-sm text-gray-500 mt-1">Co znamenají ikony ve stavovém panelu přihlášky.</p>
            </div>
            <button type="button" @click="statusLegendOpen = false"
                class="inline-flex h-9 w-9 items-center justify-center rounded-full text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600"
                aria-label="Zavřít">
                <span class="material-symbols-rounded text-[20px]">close</span>
            </button>
        </div>
        <div class="px-6 py-5 space-y-4">
            <div class="flex items-start gap-3">
                <span class="material-symbols-rounded text-green-500 text-[20px]">check_circle</span>
                <div>
                    <p class="font-bold text-sm text-gray-900">Splněno</p>
                    <p class="text-sm text-gray-500">Část přihlášky je vyplněná nebo potvrzená.</p>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <span class="material-symbols-rounded text-blue-400 text-[20px]">pending</span>
                <div>
                    <p class="font-bold text-sm text-gray-900">Čeká na školu</p>
                    <p class="text-sm text-gray-500">Údaje nebo příloha jsou doplněné a čekají na kontrolu školy.</p>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <span class="material-symbols-rounded text-orange-500 text-[20px]">error</span>
                <div>
                    <p class="font-bold text-sm text-gray-900">Je potřeba doplnit</p>
                    <p class="text-sm text-gray-500">Tato část ještě není hotová a můžete ji doplnit.</p>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <span class="material-symbols-rounded text-gray-400 text-[20px]">lock</span>
                <div>
                    <p class="font-bold text-sm text-gray-900">Uzamčeno</p>
                    <p class="text-sm text-gray-500">Sekci už není možné upravovat.</p>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <span class="material-symbols-rounded text-school-primary text-[20px]">cancel</span>
                <div>
                    <p class="font-bold text-sm text-gray-900">Nesplněno po termínu</p>
                    <p class="text-sm text-gray-500">Termín uplynul a část přihlášky nebyla dokončena včas.</p>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <span class="material-symbols-rounded text-amber-500 text-[20px]">verified</span>
                <div>
                    <p class="font-bold text-sm text-gray-900">Přihláška dokončena včas</p>
                    <p class="text-sm text-gray-500">Všechny povinné části z vaší strany byly doplněny do termínu.</p>
                </div>
            </div>
        </div>
    </div>
</div>
