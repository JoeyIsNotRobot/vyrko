<div
    x-data="loadingModal()"
    x-show="show"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @keydown.escape.window="if (show && !error) $event.preventDefault()"
    @open-loading-modal.window="open($event.detail.steps)"
    @advance-loading-modal.window="advance($event.detail.step)"
    @succeed-loading-modal.window="succeed()"
    @fail-loading-modal.window="fail($event.detail.message)"
    class="fixed inset-0 z-50 backdrop-blur-md bg-black/60 flex items-center justify-center"
    role="dialog"
    aria-modal="true"
    aria-label="Processando seu currículo"
    style="display: none;"
>
    <div class="bg-slate-800 rounded-xl p-8 w-full max-w-md mx-4 shadow-2xl border border-slate-700">
        <p class="text-white font-bold text-lg text-center">Processando...</p>

        <div class="h-2 rounded-full bg-slate-700 w-full mt-4">
            <div
                class="h-2 rounded-full bg-gradient-to-r from-blue-600 to-emerald-500 transition-all duration-500"
                :style="'width: ' + progress + '%'"
            ></div>
        </div>

        <p class="text-sm text-slate-400 mt-3 text-center" x-text="currentStep"></p>

        <div x-show="error" class="bg-red-900/40 border border-red-500/50 rounded-lg p-4 mt-4 text-center">
            <p class="text-red-300 text-sm" x-text="error"></p>
            <button type="button" class="btn danger mt-3" @click="error = null; progress = 0; show = false">
                Tentar novamente
            </button>
        </div>
    </div>
</div>
