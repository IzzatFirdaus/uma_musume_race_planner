{{--
    Reconnection prompt modal for Account runs.

    Account-mode plans live on the server. When the browser goes offline, the
    user can still browse an Account run, but any edit will silently fail.
    This modal makes that boundary explicit instead of letting edits vanish.

    Driven by the `connection` store's `reconnectPromptOpen` flag, raised by
    the store when a Livewire request fails for an Account run. A Local run is
    unaffected — it persists to localStorage regardless of connectivity — so
    the store does not raise this prompt in that case.
--}}

<div
    x-data
    x-show="$store.connection.reconnectPromptOpen"
    x-cloak
    x-trap.inert.noscroll="$store.connection.reconnectPromptOpen"
    x-on:keydown.escape.window="$store.connection.dismissReconnectPrompt()"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 focus:outline-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="reconnection-prompt-title"
    aria-describedby="reconnection-prompt-description"
    data-testid="reconnection-prompt-modal"
>
    <div
        x-show="$store.connection.reconnectPromptOpen"
        x-transition:enter="motion-safe:ease-out motion-safe:duration-200 motion-reduce:duration-0"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="motion-safe:ease-in motion-safe:duration-150 motion-reduce:duration-0"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-950/50 dark:bg-slate-950/70 motion-reduce:transition-none"
        x-on:click="$store.connection.dismissReconnectPrompt()"
        aria-hidden="true"
    ></div>

    <div
        x-show="$store.connection.reconnectPromptOpen"
        x-transition:enter="motion-safe:ease-out motion-safe:duration-200 motion-reduce:duration-0"
        x-transition:enter-start="opacity-0 scale-95 motion-reduce:scale-100"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="motion-safe:ease-in motion-safe:duration-150 motion-reduce:duration-0"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95 motion-reduce:scale-100"
        class="relative w-full max-w-lg rounded-xl border border-slate-200 bg-white shadow-xl dark:border-slate-700 dark:bg-slate-900 motion-reduce:transform-none motion-reduce:transition-none"
    >
        <div class="flex items-start gap-4 px-6 py-5">
            <svg
                class="mt-0.5 h-6 w-6 flex-none text-amber-600 dark:text-amber-400"
                fill="none"
                stroke="currentColor"
                stroke-width="1.5"
                viewBox="0 0 24 24"
                aria-hidden="true"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"
                />
            </svg>

            <div class="flex-1">
                <h2 id="reconnection-prompt-title" class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                    Connection lost
                </h2>
                <div id="reconnection-prompt-description" class="mt-2 space-y-3 text-sm text-slate-600 dark:text-slate-300">
                    <p>
                        This is an <strong>Account</strong> career run, so your changes are stored on the
                        server. The server is currently unreachable, so edits made now
                        <strong>will not be saved</strong>.
                    </p>
                    <p x-show="$store.connection.failedMessage" x-cloak>
                        <span class="font-mono text-xs" x-text="$store.connection.failedMessage"></span>
                    </p>
                    <p>
                        Copy anything you want to keep before retrying. Local career runs are unaffected —
                        they are saved in this browser.
                    </p>
                </div>
            </div>
        </div>

        <div class="flex flex-col-reverse gap-2 border-t border-slate-200 px-6 py-4 sm:flex-row sm:justify-end dark:border-slate-700">
            <button
                type="button"
                x-on:click="$store.connection.dismissReconnectPrompt()"
                class="inline-flex min-h-11 items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800 dark:focus-visible:ring-sky-400 dark:focus-visible:ring-offset-slate-900"
                data-testid="reconnection-prompt-dismiss"
            >
                Dismiss
            </button>
            <button
                type="button"
                x-on:click="$store.connection.retry()"
                class="inline-flex min-h-11 items-center justify-center rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:bg-sky-600 dark:hover:bg-sky-500 dark:focus-visible:ring-sky-400 dark:focus-visible:ring-offset-slate-900"
                data-testid="reconnection-prompt-retry"
            >
                Retry connection
            </button>
        </div>
    </div>
</div>
