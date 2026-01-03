{{-- Skill Search Autocomplete Component --}}
{{-- Implements FR-4.4, FR-4B.1-FR-4B.5: Accessible autocomplete with EN+JP matching --}}
<div class="skill-search position-relative" x-data="skillSearchHandler()" data-testid="skill-search">
    {{-- Search Input --}}
    <div class="input-group">
        <span class="input-group-text bg-transparent">
            <i class="bi bi-search" aria-hidden="true"></i>
        </span>
        <input type="text" class="form-control" placeholder="Search skills (EN or JP)..."
            wire:model.live.debounce.300ms="query" x-on:focus="$wire.openDropdown()"
            x-on:keydown.arrow-down.prevent="$wire.handleKeydown('ArrowDown')"
            x-on:keydown.arrow-up.prevent="$wire.handleKeydown('ArrowUp')"
            x-on:keydown.enter.prevent="$wire.handleKeydown('Enter')"
            x-on:keydown.escape="$wire.handleKeydown('Escape')" role="combobox" aria-autocomplete="list"
            aria-expanded="{{ $showDropdown ? 'true' : 'false' }}" aria-controls="skill-search-listbox"
            aria-activedescendant="{{ $highlightedIndex >= 0 ? 'skill-option-' . $highlightedIndex : '' }}"
            aria-label="Search for skills" data-testid="skill-search-input" id="skill-search-input">
        @if ($query)
            <button type="button" class="btn btn-outline-secondary" wire:click="$set('query', '')"
                aria-label="Clear search">
                <i class="bi bi-x" aria-hidden="true"></i>
            </button>
        @endif
    </div>

    {{-- Loading indicator --}}
    <div wire:loading wire:target="query" class="position-absolute end-0 top-50 translate-middle-y me-5">
        <span class="spinner-border spinner-border-sm text-primary" role="status" aria-hidden="true"></span>
        <span class="visually-hidden">Searching...</span>
    </div>

    {{-- Results Dropdown (FR-4B.5: listbox pattern) --}}
    @if ($showDropdown)
        <div class="position-absolute w-100 mt-1 z-3" x-on:click.outside="$wire.closeDropdown()">
            <ul class="list-group shadow-lg" role="listbox" id="skill-search-listbox" aria-label="Skill search results"
                data-testid="skill-search-results">
                @forelse ($this->results as $index => $skill)
                    <li class="list-group-item list-group-item-action d-flex justify-content-between align-items-start cursor-pointer {{ $highlightedIndex === $index ? 'active' : '' }}"
                        role="option" id="skill-option-{{ $index }}"
                        aria-selected="{{ $highlightedIndex === $index ? 'true' : 'false' }}"
                        wire:click="selectSkill({{ $skill['id'] }})" wire:key="skill-result-{{ $skill['id'] }}"
                        data-testid="skill-option-{{ $skill['id'] }}">
                        <div class="me-auto">
                            <div class="fw-bold">
                                {{ $skill['name'] }}
                                @if ($skill['name_jp'])
                                    <small class="text-muted ms-1">({{ $skill['name_jp'] }})</small>
                                @endif
                            </div>
                            @if ($skill['description'])
                                <small
                                    class="{{ $highlightedIndex === $index ? 'text-white-50' : 'text-muted' }}">{{ Str::limit($skill['description'], 60) }}</small>
                            @endif
                        </div>
                        <div class="text-end">
                            @if ($skill['type'])
                                <span class="badge bg-secondary mb-1">{{ $skill['type'] }}</span>
                            @endif
                            @if ($skill['sp_cost'])
                                <div class="small {{ $highlightedIndex === $index ? 'text-white' : 'text-primary' }}">
                                    {{ $skill['sp_cost'] }} SP
                                </div>
                            @endif
                        </div>
                    </li>
                @empty
                    {{-- FR-4B.8: Show "No results" state --}}
                    <li class="list-group-item text-center text-muted py-3" role="option" aria-disabled="true"
                        data-testid="skill-search-no-results">
                        <i class="bi bi-search me-2" aria-hidden="true"></i>
                        No skills found for "{{ $query }}"
                    </li>
                @endforelse
            </ul>
        </div>
    @endif

    {{-- Screen reader announcement --}}
    <div class="visually-hidden" role="status" aria-live="polite" aria-atomic="true">
        @if ($showDropdown && count($this->results) > 0)
            {{ count($this->results) }} skill{{ count($this->results) !== 1 ? 's' : '' }} found
        @elseif ($showDropdown && strlen($query) >= 2)
            No skills found
        @endif
    </div>
</div>

@script
    <script>
        Alpine.data('skillSearchHandler', () => ({
            init() {
                // Handle click outside to close dropdown
                document.addEventListener('click', (e) => {
                    if (!this.$el.contains(e.target)) {
                        $wire.closeDropdown();
                    }
                });
            }
        }));
    </script>
@endscript

<style>
    .skill-search .list-group-item {
        cursor: pointer;
    }

    .skill-search .list-group-item:hover:not(.active) {
        background-color: var(--bs-tertiary-bg);
    }
</style>
