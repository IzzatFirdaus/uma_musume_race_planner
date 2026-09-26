@props([
    'title' => '',
    'titleId' => 'modal-title',
    'descriptionId' => 'modal-description',
    'ariaLabel' => null,
    'testId' => 'modal',
    'maxWidth' => 'max-w-lg',
])

@php
    $wireModel = $attributes->wire('model')->value();
@endphp

<div
    x-data="{
        open: @entangle($wireModel).live,
        lastActiveElement: null,
        init() {
            this.$watch('open', (value) => {
                if (value) {
                    this.lastActiveElement = document.activeElement instanceof HTMLElement ? document.activeElement : null;

                    this.$nextTick(() => {
                        this.$refs.closeButton?.focus({ preventScroll: true });
                    });
                } else if (this.lastActiveElement instanceof HTMLElement) {
                    this.$nextTick(() => {
                        this.lastActiveElement.focus({ preventScroll: true });
                    });
                }
            });
        },
        close() {
            this.open = false;
        },
    }"
    x-show="open"
    x-cloak
    x-trap.inert.noscroll="open"
    x-on:keydown.escape.window.prevent="close()"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 focus:outline-none"
    role="dialog"
    aria-modal="true"
    @if ($title !== '')
        aria-labelledby="{{ $titleId }}"
    @else
        aria-label="{{ $ariaLabel ?? 'Dialog' }}"
    @endif
    aria-describedby="{{ $descriptionId }}"
    data-testid="{{ $testId }}"
    {{ $attributes->except(['wire:model', 'wire:model.live']) }}
>
    <div
        x-show="open"
        x-transition:enter="motion-safe:ease-out motion-safe:duration-200 motion-reduce:duration-0"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="motion-safe:ease-in motion-safe:duration-150 motion-reduce:duration-0"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-950/50 dark:bg-slate-950/70 motion-reduce:transition-none"
        x-on:click="close()"
        aria-hidden="true"
    ></div>

    <div
        x-show="open"
        x-transition:enter="motion-safe:ease-out motion-safe:duration-200 motion-reduce:duration-0"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="motion-safe:ease-in motion-safe:duration-150 motion-reduce:duration-0"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="relative w-full {{ $maxWidth }} rounded-xl border border-slate-200 bg-white shadow-xl dark:border-slate-700 dark:bg-slate-900 motion-reduce:transform-none motion-reduce:transition-none"
    >
        @if ($title !== '')
            <div class="flex items-center justify-between gap-4 border-b border-slate-200 px-6 py-4 dark:border-slate-700">
                <h2 id="{{ $titleId }}" class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                    {{ $title }}
                </h2>
                <button
                    type="button"
                    class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200 dark:focus-visible:ring-sky-400 dark:focus-visible:ring-offset-slate-900"
                    x-ref="closeButton"
                    x-on:click="close()"
                    aria-label="Close modal"
                    data-testid="{{ $testId }}-close-btn"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        <div id="{{ $descriptionId }}" class="w-full">
            {{ $slot }}
        </div>
    </div>
</div>
