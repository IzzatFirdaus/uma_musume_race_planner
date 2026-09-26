{{--
    Toast notification container.

    A global, always-mounted aria-live region. Toasts arrive from three sources,
    all of which funnel into the Alpine `toast` store
    (resources/js/stores/index.js):

      1. window `toast` CustomEvent      (client-side JS, e.g. quick-create-plan)
      2. Livewire `toast` event          (server components: Manager, TurnTracker, …)
      3. Alpine `toast.add()` calls      (components holding a reference)

    This is intentionally a plain container rather than the Livewire
    `App\Livewire\Common\Toast` component, so that toasts raised by the server
    and by client-side JS are announced identically.
--}}

<div
    x-data
    class="pointer-events-none fixed inset-x-0 bottom-0 z-[60] flex flex-col items-center gap-2 p-4 sm:items-end sm:p-6"
    data-testid="toast-container"
>
    {{-- Polite announcements. Errors use the assertive region below. --}}
    <div
        class="sr-only"
        role="status"
        aria-live="polite"
        aria-atomic="false"
        data-testid="toast-live-polite"
    >
        <template x-for="toast in $store.toast.items.filter((t) => t.type !== 'error')" :key="`polite-${toast.id}`">
            <span x-text="toast.message"></span>
        </template>
    </div>

    <div
        class="sr-only"
        role="alert"
        aria-live="assertive"
        aria-atomic="true"
        data-testid="toast-live-assertive"
    >
        <template x-for="toast in $store.toast.items.filter((t) => t.type === 'error')" :key="`assertive-${toast.id}`">
            <span x-text="toast.message"></span>
        </template>
    </div>

    {{-- Visible toasts --}}
    <template x-for="toast in $store.toast.items" :key="toast.id">
        <div
            x-transition:enter="motion-safe:ease-out motion-safe:duration-200 motion-reduce:duration-0"
            x-transition:enter-start="opacity-0 translate-y-2 sm:translate-y-0 sm:translate-x-2"
            x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0"
            x-transition:leave="motion-safe:ease-in motion-safe:duration-150 motion-reduce:duration-0"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0 translate-y-2"
            class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-xl border px-4 py-3 shadow-lg motion-reduce:transition-none"
            :class="{
                'border-emerald-300 bg-emerald-50 text-emerald-900 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-100': toast.type === 'success',
                'border-red-300 bg-red-50 text-red-900 dark:border-red-800 dark:bg-red-950 dark:text-red-100': toast.type === 'error' || toast.type === 'danger',
                'border-amber-300 bg-amber-50 text-amber-900 dark:border-amber-800 dark:bg-amber-950 dark:text-amber-100': toast.type === 'warning',
                'border-sky-300 bg-sky-50 text-sky-900 dark:border-sky-800 dark:bg-sky-950 dark:text-sky-100': toast.type === 'info',
            }"
            :data-testid="`toast-${toast.id}`"
            :data-toast-type="toast.type"
        >
            <svg
                class="mt-0.5 h-5 w-5 flex-none"
                fill="none"
                stroke="currentColor"
                stroke-width="1.5"
                viewBox="0 0 24 24"
                aria-hidden="true"
            >
                <template x-if="toast.type === 'success'">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </template>
                <template x-if="toast.type === 'error' || toast.type === 'danger'">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </template>
                <template x-if="toast.type === 'warning'">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </template>
                <template x-if="toast.type === 'info'">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                </template>
            </svg>

            <p class="flex-1 text-sm font-medium" x-text="toast.message"></p>

            <button
                type="button"
                x-on:click="$store.toast.dismiss(toast.id)"
                class="-m-1 inline-flex min-h-11 min-w-11 flex-none items-center justify-center rounded-lg p-1 opacity-70 hover:opacity-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-current"
                :aria-label="`Dismiss notification: ${toast.message}`"
                :data-testid="`toast-dismiss-${toast.id}`"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </template>
</div>
