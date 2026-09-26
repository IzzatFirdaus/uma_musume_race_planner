{{--
    Keyboard shortcuts help modal.

    Self-contained Alpine modal driven by the `keyboard` store
    (resources/js/stores/index.js), which owns shortcut registration and the
    open/close state of this dialog. Using the store rather than
    `x-common.modal` keeps the dialog purely client-side — there is no server
    state to entangle.

    Opened with "?" on most keyboards. Focus is trapped while open and returned
    to the previously focused element on close.
--}}

<div
    x-data
    x-show="$store.keyboard.helpOpen"
    x-cloak
    x-trap.inert.noscroll="$store.keyboard.helpOpen"
    x-on:keydown.escape.window="$store.keyboard.helpOpen && $store.keyboard.closeHelp()"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 focus:outline-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="keyboard-shortcuts-title"
    aria-describedby="keyboard-shortcuts-description"
    data-testid="keyboard-shortcuts-help"
>
    <div
        x-show="$store.keyboard.helpOpen"
        x-transition:enter="motion-safe:ease-out motion-safe:duration-200 motion-reduce:duration-0"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="motion-safe:ease-in motion-safe:duration-150 motion-reduce:duration-0"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-950/50 dark:bg-slate-950/70 motion-reduce:transition-none"
        x-on:click="$store.keyboard.closeHelp()"
        aria-hidden="true"
    ></div>

    <div
        x-show="$store.keyboard.helpOpen"
        x-transition:enter="motion-safe:ease-out motion-safe:duration-200 motion-reduce:duration-0"
        x-transition:enter-start="opacity-0 scale-95 motion-reduce:scale-100"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="motion-safe:ease-in motion-safe:duration-150 motion-reduce:duration-0"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95 motion-reduce:scale-100"
        class="relative w-full max-w-lg rounded-xl border border-slate-200 bg-white shadow-xl dark:border-slate-700 dark:bg-slate-900 motion-reduce:transform-none motion-reduce:transition-none"
    >
        <div class="flex items-center justify-between gap-4 border-b border-slate-200 px-6 py-4 dark:border-slate-700">
            <h2 id="keyboard-shortcuts-title" class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                Keyboard shortcuts
            </h2>
            <button
                type="button"
                class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200 dark:focus-visible:ring-sky-400 dark:focus-visible:ring-offset-slate-900"
                x-on:click="$store.keyboard.closeHelp()"
                aria-label="Close keyboard shortcuts"
                data-testid="keyboard-shortcuts-help-close"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div id="keyboard-shortcuts-description" class="max-h-[60vh] overflow-y-auto px-6 py-4">
            <dl class="divide-y divide-slate-200 dark:divide-slate-700">
                <template x-for="shortcut in $store.keyboard.shortcuts" :key="shortcut.id">
                    <div class="flex items-center justify-between gap-4 py-3" :data-testid="`shortcut-${shortcut.id}`">
                        <dt class="text-sm text-slate-700 dark:text-slate-300" x-text="shortcut.description"></dt>
                        <dd class="flex flex-none flex-wrap justify-end gap-1">
                            <template x-for="key in shortcut.keys" :key="key">
                                <kbd
                                    class="rounded border border-slate-300 bg-slate-100 px-1.5 py-0.5 font-mono text-xs font-semibold text-slate-800 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200"
                                    x-text="key"
                                ></kbd>
                            </template>
                        </dd>
                    </div>
                </template>
            </dl>
        </div>

        <div class="border-t border-slate-200 px-6 py-3 dark:border-slate-700">
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Shortcuts are ignored while typing in a text field.
            </p>
        </div>
    </div>
</div>
