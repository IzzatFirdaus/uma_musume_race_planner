<div
    x-data="{
        showRecentTurns: {{ $showRecentTurns ? 'true' : 'false' }},
        toggleRecentTurns() {
            this.showRecentTurns = !this.showRecentTurns;
        }
    }"
    x-on:turn-logged.window="showRecentTurns = true"
    class="space-y-6"
>
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                Log Turn Stats
            </h3>
            <button
                x-on:click="toggleRecentTurns()"
                class="rounded-md px-3 py-1.5 text-sm font-medium text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700"
                data-testid="turn-tracker-toggle-recent"
            >
                <span x-show="!showRecentTurns">Show Recent Turns</span>
                <span x-show="showRecentTurns">Hide Recent Turns</span>
            </button>
        </div>

        <form wire:submit.prevent="logTurn" class="space-y-4">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Speed -->
                <div>
                    <label for="turn_speed" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Speed <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <input
                        type="number"
                        id="turn_speed"
                        wire:model.live="speed"
                        min="0"
                        max="1200"
                        required
                        aria-describedby="speedFeedback"
                        data-testid="turn-tracker-speed-input"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 @error('speed') border-red-500 @enderror"
                    >
                    @error('speed')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400" id="speedFeedback">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Stamina -->
                <div>
                    <label for="turn_stamina" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Stamina <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <input
                        type="number"
                        id="turn_stamina"
                        wire:model.live="stamina"
                        min="0"
                        max="1200"
                        required
                        aria-describedby="staminaFeedback"
                        data-testid="turn-tracker-stamina-input"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 @error('stamina') border-red-500 @enderror"
                    >
                    @error('stamina')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400" id="staminaFeedback">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Power -->
                <div>
                    <label for="turn_power" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Power <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <input
                        type="number"
                        id="turn_power"
                        wire:model.live="power"
                        min="0"
                        max="1200"
                        required
                        aria-describedby="powerFeedback"
                        data-testid="turn-tracker-power-input"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 @error('power') border-red-500 @enderror"
                    >
                    @error('power')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400" id="powerFeedback">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Guts -->
                <div>
                    <label for="turn_guts" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Guts <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <input
                        type="number"
                        id="turn_guts"
                        wire:model.live="guts"
                        min="0"
                        max="1200"
                        required
                        aria-describedby="gutsFeedback"
                        data-testid="turn-tracker-guts-input"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 @error('guts') border-red-500 @enderror"
                    >
                    @error('guts')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400" id="gutsFeedback">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Wit -->
                <div>
                    <label for="turn_wit" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Wit <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <input
                        type="number"
                        id="turn_wit"
                        wire:model.live="wit"
                        min="0"
                        max="1200"
                        required
                        aria-describedby="witFeedback"
                        data-testid="turn-tracker-wit-input"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 @error('wit') border-red-500 @enderror"
                    >
                    @error('wit')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400" id="witFeedback">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Stamina Percentage -->
                <div>
                    <label for="turn_stamina_percentage" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Stamina % <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <div class="flex items-center gap-3">
                        <input
                            type="range"
                            id="turn_stamina_percentage_slider"
                            x-model="$wire.staminaPercentage"
                            min="0"
                            max="100"
                            class="flex-1 h-2 rounded-lg appearance-none bg-gray-200 accent-blue-600 dark:bg-gray-700"
                            data-testid="turn-tracker-stamina-slider"
                        >
                        <input
                            type="number"
                            id="turn_stamina_percentage"
                            wire:model.live="staminaPercentage"
                            min="0"
                            max="100"
                            required
                            aria-describedby="staminaPercentageFeedback"
                            data-testid="turn-tracker-stamina-percentage-input"
                            class="w-20 rounded-lg border border-gray-300 bg-white px-3 py-2 text-center text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 @error('staminaPercentage') border-red-500 @enderror"
                        >
                    </div>
                    @error('staminaPercentage')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400" id="staminaPercentageFeedback">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end pt-2">
                <button
                    type="submit"
                    class="rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-medium text-white hover:bg-blue-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-500 disabled:opacity-60 dark:bg-blue-500 dark:hover:bg-blue-600"
                    wire:loading.attr="disabled"
                    wire:target="logTurn"
                    data-testid="turn-tracker-log-btn"
                >
                    <span wire:loading.remove wire:target="logTurn">Log Turn</span>
                    <span wire:loading wire:target="logTurn">Logging...</span>
                </button>
            </div>
        </form>

        <!-- Recent Turns Preview (Collapsible) -->
        <div
            x-show="showRecentTurns"
            x-transition
            class="mt-6 border-t border-gray-200 pt-4 dark:border-gray-700"
            data-testid="turn-tracker-recent-turns"
        >
            <h4 class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">Recent Turns</h4>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th scope="col" class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Turn
                            </th>
                            <th scope="col" class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Speed
                            </th>
                            <th scope="col" class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Stamina
                            </th>
                            <th scope="col" class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Power
                            </th>
                            <th scope="col" class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Guts
                            </th>
                            <th scope="col" class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Wit
                            </th>
                            <th scope="col" class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Stamina %
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                        @forelse($plan->turns()->orderBy('turn_number', 'desc')->take(5)->get() as $turn)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="whitespace-nowrap px-4 py-2 text-sm text-gray-900 dark:text-gray-100">
                                    {{ $turn->turn_number }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-2 text-sm text-gray-900 dark:text-gray-100">
                                    {{ $turn->speed }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-2 text-sm text-gray-900 dark:text-gray-100">
                                    {{ $turn->stamina }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-2 text-sm text-gray-900 dark:text-gray-100">
                                    {{ $turn->power }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-2 text-sm text-gray-900 dark:text-gray-100">
                                    {{ $turn->guts }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-2 text-sm text-gray-900 dark:text-gray-100">
                                    {{ $turn->wit }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-2 text-sm text-gray-900 dark:text-gray-100">
                                    {{ $turn->stamina_percentage }}%
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No turns logged yet
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
