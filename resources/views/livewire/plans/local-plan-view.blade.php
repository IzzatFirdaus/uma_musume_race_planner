<div
    data-testid="local-plan-view"
    x-data="{
        loadRunFromStorage(detail) {
            const store = window.localRunStorage;

            if (!store) {
                this.$wire.loadRun({});
                return;
            }

            this.$wire.loadRun(store.getByUuid(detail?.uuid) ?? {});
        },

        persistRun(detail) {
            const store = window.localRunStorage;

            if (!store) {
                this.$wire.saveFailed('Browser storage is unavailable.');
                return;
            }

            try {
                store.update(detail.uuid, { career_run: detail.run.career_run });
                this.$wire.dispatch('local-run-saved');
            } catch (error) {
                this.$wire.saveFailed(error?.message ?? 'Could not save to browser storage.');
            }
        },

        removeRun(detail) {
            window.localRunStorage?.delete(detail?.uuid);
        },

        openConvert() {
            this.$wire.dispatch('convert-local-run-requested', { uuid: @js($uuid) });
        },
    }"
    x-on:load-local-run.window="loadRunFromStorage($event.detail)"
    x-on:save-local-run.window="persistRun($event.detail)"
    x-on:delete-local-run.window="removeRun($event.detail)"
    x-on:open-convert-modal.window="openConvert($event.detail)"
>
    {{-- Storage warning: this run exists only in this browser --}}
    <div
        class="mb-4 flex items-start gap-3 rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-800 dark:bg-amber-950 dark:text-amber-100"
        role="note"
        data-testid="local-storage-warning"
    >
        <svg class="mt-0.5 h-5 w-5 flex-none" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
        </svg>
        <div>
            <p class="font-semibold">This career run is stored only in this browser.</p>
            <p class="mt-1">
                Clearing site data, using a different browser or device, or browsing in private mode
                will lose it. Convert it to an Account run to keep a server-side copy.
            </p>
        </div>
    </div>

    {{-- Header --}}
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100" data-testid="local-plan-title">
                    {{ $form['plan_title'] ?? $form['name'] ?? 'Untitled Plan' }}
                </h1>
                <span
                    class="inline-flex items-center rounded-full bg-sky-100 px-2.5 py-0.5 text-xs font-semibold text-sky-800 dark:bg-sky-950 dark:text-sky-200"
                    data-testid="storage-mode-badge"
                >
                    Local
                </span>
            </div>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                <span x-text="`Run ${@js($uuid).slice(0, 8)}…`"></span>
                @if ($updatedAt)
                    <span> · Updated {{ \Illuminate\Support\Carbon::parse($updatedAt)->diffForHumans() }}</span>
                @endif
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a
                href="/plans"
                class="inline-flex min-h-11 items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800 dark:focus-visible:ring-sky-400 dark:focus-visible:ring-offset-slate-900"
                data-testid="back-to-dashboard"
            >
                Back to Dashboard
            </a>

            @unless ($isEditMode)
                <a
                    href="{{ route('plans.local.edit', ['uuid' => $uuid]) }}"
                    class="inline-flex min-h-11 items-center justify-center rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-sky-400 dark:focus-visible:ring-offset-slate-900"
                    data-testid="local-plan-edit-button"
                >
                    Edit
                </a>
            @endunless

            <button
                type="button"
                x-on:click="openConvert()"
                class="inline-flex min-h-11 items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800 dark:focus-visible:ring-sky-400 dark:focus-visible:ring-offset-slate-900"
                data-testid="convert-to-account-button"
            >
                Convert to Account
            </button>
        </div>
    </div>

    {{-- Loading --}}
    <div
        x-show="$wire.isLoading"
        x-cloak
        class="rounded-xl border border-slate-200 p-6 dark:border-slate-700"
        data-testid="local-plan-loading"
    >
        <p class="text-slate-600 dark:text-slate-400">Loading career run from this browser…</p>
    </div>

    {{-- Not found --}}
    @if ($notFound)
        <div
            class="rounded-xl border border-red-300 bg-red-50 p-6 dark:border-red-800 dark:bg-red-950"
            role="alert"
            data-testid="local-plan-not-found"
        >
            <h2 class="text-lg font-semibold text-red-900 dark:text-red-100">Career run not found</h2>
            <p class="mt-2 text-sm text-red-800 dark:text-red-200">
                No Local run with this identifier exists in this browser. It may have been deleted, or
                it may belong to a different browser or device.
            </p>
            <a href="/local-data" class="mt-4 inline-flex min-h-11 items-center text-sm font-semibold underline" data-testid="local-plan-not-found-link">
                Manage Local career runs
            </a>
        </div>
    @endif

    @unless ($notFound)
        <form
            wire:submit="save"
            class="space-y-6"
            x-data="{ draft: null }"
            x-on:input="$wire.markDirty()"
            data-testid="local-plan-form"
        >
            @error('run')
                <p class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800 dark:bg-red-900/40 dark:text-red-200" role="alert">
                    {{ $message }}
                </p>
            @enderror

            {{-- Career run details --}}
            <section
                class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-900"
                aria-labelledby="career-details-heading"
            >
                <h2 id="career-details-heading" class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                    Career details
                </h2>

                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label for="local-plan-title-field" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Plan title</label>
                        <input
                            id="local-plan-title-field"
                            type="text"
                            name="plan_title"
                            wire:model="form.plan_title"
                            @readonly(!$isEditMode)
                            @disabled(!$isEditMode)
                            class="mt-1 block w-full min-h-11 rounded-lg border border-slate-300 px-3 py-2 text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100"
                            data-testid="input-plan-title"
                        />
                        @error('form.plan_title') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="local-plan-name-field" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Trainee name <span class="text-red-600" aria-hidden="true">*</span></label>
                        <input
                            id="local-plan-name-field"
                            type="text"
                            name="name"
                            wire:model="form.name"
                            @readonly(!$isEditMode)
                            @disabled(!$isEditMode)
                            required
                            class="mt-1 block w-full min-h-11 rounded-lg border border-slate-300 px-3 py-2 text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100"
                            data-testid="input-name"
                        />
                        @error('form.name') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="local-career-stage-field" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Career stage</label>
                        <select
                            id="local-career-stage-field"
                            name="career_stage"
                            wire:model="form.career_stage"
                            @disabled(!$isEditMode)
                            class="mt-1 block w-full min-h-11 rounded-lg border border-slate-300 px-3 py-2 text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100"
                            data-testid="input-career-stage"
                        >
                            <option value="">Not set</option>
                            <option value="junior">Junior</option>
                            <option value="classic">Classic</option>
                            <option value="senior">Senior</option>
                        </select>
                        @error('form.career_stage') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="local-current-turn-field" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Current turn</label>
                        <input
                            id="local-current-turn-field"
                            type="number"
                            min="1"
                            max="78"
                            wire:model="form.current_turn"
                            @readonly(!$isEditMode)
                            @disabled(!$isEditMode)
                            class="mt-1 block w-full min-h-11 rounded-lg border border-slate-300 px-3 py-2 text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100"
                            data-testid="input-current-turn"
                        />
                        @error('form.current_turn') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="local-sp-field" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Skill points available</label>
                        <input
                            id="local-sp-field"
                            type="number"
                            min="0"
                            wire:model="form.total_sp_available"
                            @readonly(!$isEditMode)
                            @disabled(!$isEditMode)
                            class="mt-1 block w-full min-h-11 rounded-lg border border-slate-300 px-3 py-2 text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100"
                            data-testid="input-total-sp"
                        />
                        @error('form.total_sp_available') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="local-stamina-field" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Stamina (%)</label>
                        <input
                            id="local-stamina-field"
                            type="number"
                            min="0"
                            max="100"
                            wire:model="form.stamina_percentage"
                            @readonly(!$isEditMode)
                            @disabled(!$isEditMode)
                            class="mt-1 block w-full min-h-11 rounded-lg border border-slate-300 px-3 py-2 text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100"
                            data-testid="input-stamina"
                        />
                        @error('form.stamina_percentage') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            {{-- Stat progress --}}
            <section
                class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-900"
                aria-labelledby="stat-progress-heading"
                data-testid="local-stat-progress"
            >
                <h2 id="stat-progress-heading" class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                    Stat progress
                    <span class="ml-2 text-sm font-normal text-slate-500 dark:text-slate-400">
                        {{ count($statProgress) }} turn(s) logged
                    </span>
                </h2>

                @if (count($statProgress) === 0)
                    <p class="mt-3 text-sm text-slate-600 dark:text-slate-400">
                        No turns have been logged for this run yet.
                    </p>
                @else
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                            <caption class="sr-only">Stat values for each logged turn</caption>
                            <thead>
                                <tr class="text-left">
                                    <th scope="col" class="py-2 pr-4 font-semibold text-slate-700 dark:text-slate-300">Turn</th>
                                    <th scope="col" class="py-2 pr-4 font-semibold text-slate-700 dark:text-slate-300">Speed</th>
                                    <th scope="col" class="py-2 pr-4 font-semibold text-slate-700 dark:text-slate-300">Stamina</th>
                                    <th scope="col" class="py-2 pr-4 font-semibold text-slate-700 dark:text-slate-300">Power</th>
                                    <th scope="col" class="py-2 pr-4 font-semibold text-slate-700 dark:text-slate-300">Guts</th>
                                    <th scope="col" class="py-2 font-semibold text-slate-700 dark:text-slate-300">Wit</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach ($statProgress as $entry)
                                    <tr wire:key="turn-{{ $loop->index }}">
                                        <th scope="row" class="py-2 pr-4 text-left font-medium text-slate-900 dark:text-slate-100">
                                            {{ $entry['turn_number'] ?? $entry['turn'] ?? '—' }}
                                        </th>
                                        <td class="py-2 pr-4 text-slate-700 dark:text-slate-300">{{ $entry['speed'] ?? '—' }}</td>
                                        <td class="py-2 pr-4 text-slate-700 dark:text-slate-300">{{ $entry['stamina'] ?? '—' }}</td>
                                        <td class="py-2 pr-4 text-slate-700 dark:text-slate-300">{{ $entry['power'] ?? '—' }}</td>
                                        <td class="py-2 pr-4 text-slate-700 dark:text-slate-300">{{ $entry['guts'] ?? '—' }}</td>
                                        <td class="py-2 text-slate-700 dark:text-slate-300">{{ $entry['wit'] ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            {{-- Save bar --}}
            @if ($isEditMode)
                <div
                    class="sticky bottom-0 flex flex-wrap items-center justify-end gap-3 border-t border-slate-200 bg-white/95 py-3 backdrop-blur dark:border-slate-700 dark:bg-slate-950/95"
                    data-testid="local-plan-save-bar"
                >
                    <p
                        class="mr-auto text-sm text-slate-600 dark:text-slate-400"
                        x-show="$wire.isDirty"
                        x-cloak
                        role="status"
                        data-testid="unsaved-indicator"
                    >
                        You have unsaved changes.
                    </p>

                    <button
                        type="button"
                        x-on:click="$wire.reload()"
                        class="inline-flex min-h-11 items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800 dark:focus-visible:ring-sky-400"
                        data-testid="local-plan-reload"
                    >
                        Discard changes
                    </button>

                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="inline-flex min-h-11 items-center justify-center rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white disabled:opacity-60 dark:focus-visible:ring-sky-400 dark:focus-visible:ring-offset-slate-900"
                        data-testid="local-plan-save"
                    >
                        <span wire:loading.remove wire:target="save">Save</span>
                        <span wire:loading wire:target="save">Saving…</span>
                    </button>
                </div>
            @endunless
        </form>
    @endunless
</div>
