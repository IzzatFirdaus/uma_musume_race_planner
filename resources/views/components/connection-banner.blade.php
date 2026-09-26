@props([
    'store' => 'connection',
])

{{--
    Connection status banner.

    Renders nothing while online. When the browser reports offline, or a Livewire
    request has failed, an assertive banner appears so screen reader users are
    told Local edits are no longer being persisted to a server.

    Data source: Alpine store `$store.connection` (resources/js/stores/index.js).
--}}

<div
    x-data
    x-show="$store.{{ $store }}.offline || $store.{{ $store }}.failed"
    x-cloak
    x-transition:enter="motion-safe:ease-out motion-safe:duration-200 motion-reduce:duration-0"
    x-transition:enter-start="opacity-0 -translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="motion-safe:ease-in motion-safe:duration-150 motion-reduce:duration-0"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-2"
    class="relative z-50 bg-amber-100 text-amber-900 border-b border-amber-300 dark:bg-amber-950 dark:text-amber-100 dark:border-amber-800"
    role="alert"
    aria-live="assertive"
    data-testid="connection-banner"
>
    <div class="mx-auto flex max-w-7xl items-start gap-3 px-4 py-3 sm:px-6 lg:px-8">
        <svg
            class="mt-0.5 h-5 w-5 flex-none"
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
        <div class="text-sm font-medium">
            <p x-show="$store.{{ $store }}.offline && !$store.{{ $store }}.failed">
                You are offline. Local career runs are still saved in this browser, but account
                changes will not sync until the connection is restored.
            </p>
            <p x-show="$store.{{ $store }}.failed" x-cloak>
                <span x-show="$store.{{ $store }}.failedMessage" x-text="$store.{{ $store }}.failedMessage"></span>
                <span x-show="!$store.{{ $store }}.failedMessage">
                    The connection to the server was lost. Your unsaved changes may not have been stored.
                </span>
            </p>
        </div>
        <button
            type="button"
            x-show="$store.{{ $store }}.failed"
            x-cloak
            x-on:click="$store.{{ $store }}.retry()"
            class="ml-auto inline-flex min-h-11 min-w-11 flex-none items-center justify-center rounded-lg px-3 text-sm font-semibold underline hover:bg-amber-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-600 focus-visible:ring-offset-2 focus-visible:ring-offset-amber-100 dark:hover:bg-amber-900 dark:focus-visible:ring-amber-400 dark:focus-visible:ring-offset-amber-950"
            data-testid="connection-banner-retry"
        >
            Retry
        </button>
    </div>
</div>
