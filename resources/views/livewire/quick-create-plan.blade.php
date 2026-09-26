<div
    x-data="{
        handleLocalPlanCreate(detail) {
            const payload = detail[0] ?? detail;
            const planData = payload.planData ?? payload;
            const redirectUrl = payload.redirectUrl ?? `/plans/local/${planData.id}/edit`;

            try {
                if (typeof window.localRunStorage !== 'undefined') {
                    window.localRunStorage.create(planData);
                } else {
                    const storageKey = 'uma_local_runs';
                    let store = JSON.parse(localStorage.getItem(storageKey) || 'null');

                    if (!store) {
                        store = {
                            schema_version: '1.0.0',
                            runs: [],
                            last_modified: new Date().toISOString(),
                        };
                    }

                    store.runs.push(planData);
                    store.last_modified = new Date().toISOString();
                    localStorage.setItem(storageKey, JSON.stringify(store));
                }

                window.location.href = redirectUrl;
            } catch (error) {
                console.error('Failed to create local plan:', error);
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        type: 'error',
                        message: 'Failed to create local plan. Please try again.',
                    },
                }));
            }
        },
    }"
    x-on:create-local-plan.window="handleLocalPlanCreate($event.detail)"
>
    <x-common.modal
        wire:model="showModal"
        title="Create New Plan"
        title-id="createPlanModalLabel"
        test-id="quick-create-modal"
    >
        <form wire:submit.prevent="save" id="quickCreatePlanForm" novalidate class="flex flex-col">
            <div class="space-y-4 px-6 py-4">
                <div>
                    <label for="quick_title" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Plan Title <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <input
                        type="text"
                        id="quick_title"
                        wire:model.live="title"
                        placeholder="e.g., Special Week's Training Plan"
                        required
                        aria-describedby="titleFeedback"
                        data-testid="quick-create-title-input"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 @error('title') border-red-500 @enderror"
                    >
                    @error('title')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400" id="titleFeedback">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="quick_character" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Character <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <select
                        id="quick_character"
                        wire:model.live="characterId"
                        aria-describedby="characterFeedback"
                        data-testid="quick-create-character-select"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 @error('characterId') border-red-500 @enderror"
                    >
                        <option value="">-- Select Character --</option>
                        @foreach ($characters as $character)
                            <option value="{{ $character->id }}">
                                {{ $character->name }}
                                @if ($character->team)
                                    ({{ $character->team }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('characterId')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400" id="characterFeedback">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Selecting a character auto-populates growth rates and aptitudes.
                    </p>
                </div>

                <fieldset>
                    <legend class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Storage Mode
                    </legend>
                    <div class="grid grid-cols-2 gap-2" role="radiogroup" aria-label="Storage mode selection">
                        <label
                            class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border px-3 py-2 text-sm font-medium transition @if ($storageMode === 'local') border-blue-500 bg-blue-50 text-blue-800 dark:border-blue-400 dark:bg-blue-900/40 dark:text-blue-200 @else border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800 @endif"
                            data-testid="quick-create-storage-local-label"
                        >
                            <input
                                type="radio"
                                name="storageMode"
                                value="local"
                                wire:model.live="storageMode"
                                class="sr-only"
                                data-testid="quick-create-storage-local"
                            >
                            <span aria-hidden="true">📱</span>
                            Local
                        </label>

                        <label
                            class="flex items-center justify-center gap-2 rounded-lg border px-3 py-2 text-sm font-medium transition @if (!$isAuthenticated) cursor-not-allowed opacity-50 @else cursor-pointer @endif @if ($storageMode === 'account') border-green-500 bg-green-50 text-green-800 dark:border-green-400 dark:bg-green-900/40 dark:text-green-200 @else border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800 @endif"
                            data-testid="quick-create-storage-account-label"
                        >
                            <input
                                type="radio"
                                name="storageMode"
                                value="account"
                                wire:model.live="storageMode"
                                class="sr-only"
                                @disabled(!$isAuthenticated)
                                data-testid="quick-create-storage-account"
                            >
                            <span aria-hidden="true">☁️</span>
                            Account
                        </label>
                    </div>
                    @if (!$isAuthenticated)
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            <a href="{{ route('login') }}" class="text-blue-600 underline dark:text-blue-400">Sign in</a>
                            to save plans to your account.
                        </p>
                    @elseif ($storageMode === 'local')
                        <p class="mt-2 text-xs text-blue-700 dark:text-blue-300">
                            Plan will be stored in your browser's local storage.
                        </p>
                    @else
                        <p class="mt-2 text-xs text-green-700 dark:text-green-300">
                            Plan will be saved to your account.
                        </p>
                    @endif
                    @error('storageMode')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </fieldset>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label for="quick_career_stage" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Career Stage
                        </label>
                        <select
                            id="quick_career_stage"
                            wire:model.live="careerStage"
                            aria-describedby="careerStageFeedback"
                            data-testid="quick-create-career-stage-select"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                        >
                            @foreach ($careerStageOptions as $option)
                                <option value="{{ $option['value'] }}">{{ $option['text'] }}</option>
                            @endforeach
                        </select>
                        @error('careerStage')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400" id="careerStageFeedback">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="quick_trainee_class" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Class
                        </label>
                        <select
                            id="quick_trainee_class"
                            wire:model.live="traineeClass"
                            aria-describedby="classFeedback"
                            data-testid="quick-create-class-select"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                        >
                            @foreach ($classOptions as $option)
                                <option value="{{ $option['value'] }}">{{ $option['text'] }}</option>
                            @endforeach
                        </select>
                        @error('traineeClass')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400" id="classFeedback">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                <button
                    type="button"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-500 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
                    x-on:click="$wire.closeModal()"
                    data-testid="quick-create-cancel-btn"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-500 disabled:opacity-60 dark:bg-blue-500 dark:hover:bg-blue-600"
                    wire:loading.attr="disabled"
                    wire:target="save"
                    data-testid="quick-create-submit-btn"
                >
                    <span wire:loading.remove wire:target="save">Create Plan</span>
                    <span wire:loading wire:target="save">Creating...</span>
                </button>
            </div>
        </form>
    </x-common.modal>
</div>
