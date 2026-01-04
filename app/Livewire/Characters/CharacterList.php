<?php

declare(strict_types=1);

namespace App\Livewire\Characters;

use App\Models\Umamusume;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * CharacterList - Livewire page component for browsing Uma Musume characters.
 *
 * Requirements: 11.1-11.6
 * - Display all available Uma Musume characters
 * - Show name, team, rarity, base stats, growth rates, aptitudes
 * - Support filtering by name, team, rarity, specialty distance
 * - Display character images when available
 * - Support character selection for plan creation
 */
#[Layout('layouts.app')]
class CharacterList extends Component
{
    /** @var string Search query for filtering by name, team, or tags */
    #[Url(as: 'q')]
    public string $search = '';

    /** @var string Team filter */
    #[Url(as: 'team')]
    public string $teamFilter = '';

    /** @var int|string Rarity filter (1-3 stars or empty for all) */
    #[Url(as: 'rarity')]
    public int|string $rarityFilter = '';

    /** @var string Distance specialty filter */
    #[Url(as: 'distance')]
    public string $distanceFilter = '';

    /** @var string|null Currently selected character ID for modal */
    public ?string $selectedCharacterId = null;

    /** @var array|null Currently selected character data */
    public ?array $selectedCharacter = null;

    /** @var bool Whether the detail modal is open */
    public bool $showDetailModal = false;

    /**
     * Get all unique teams from the database.
     *
     * @return array<string>
     */
    public function getTeamsProperty(): array
    {
        return Umamusume::query()
            ->whereNotNull('team')
            ->where('team', '!=', '')
            ->distinct()
            ->orderBy('team')
            ->pluck('team')
            ->toArray();
    }

    /**
     * Get filtered characters based on search and filter criteria.
     *
     * @return Collection<int, Umamusume>
     */
    public function getCharactersProperty(): Collection
    {
        $query = Umamusume::query()->orderBy('name');

        // Search filter (name, team, tags)
        if ($this->search !== '') {
            $searchTerm = strtolower($this->search);
            $query->where(function ($q) use ($searchTerm) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$searchTerm}%"])
                    ->orWhereRaw('LOWER(team) LIKE ?', ["%{$searchTerm}%"])
                    ->orWhereRaw('LOWER(COALESCE(tags, "")) LIKE ?', ["%{$searchTerm}%"]);
            });
        }

        // Team filter
        if ($this->teamFilter !== '') {
            $query->where('team', $this->teamFilter);
        }

        // Rarity filter
        if ($this->rarityFilter !== '') {
            $query->where('rarity', (int) $this->rarityFilter);
        }

        // Distance specialty filter (based on aptitudes)
        if ($this->distanceFilter !== '') {
            $query->whereRaw(
                "JSON_EXTRACT(aptitudes, '$.distance.{$this->distanceFilter}') IN ('S', 'A', 'SS')"
            );
        }

        return $query->get();
    }

    /**
     * Open the character detail modal.
     */
    public function openCharacterModal(string $characterId): void
    {
        $this->selectedCharacterId = $characterId;
        $character = Umamusume::find($characterId);

        if ($character) {
            $this->selectedCharacter = $character->toArray();
            $this->showDetailModal = true;
        }
    }

    /**
     * Close the character detail modal.
     */
    public function closeCharacterModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedCharacterId = null;
        $this->selectedCharacter = null;
    }

    /**
     * Clear all filters.
     */
    public function clearFilters(): void
    {
        $this->search = '';
        $this->teamFilter = '';
        $this->rarityFilter = '';
        $this->distanceFilter = '';
    }

    /**
     * Dispatch event to select character for plan creation.
     * This emits an event that can be listened to by plan creation components.
     */
    public function selectForPlan(string $characterId): void
    {
        $character = Umamusume::find($characterId);

        if ($character) {
            $this->dispatch('character-selected', [
                'id' => $character->id,
                'name' => $character->name,
                'growth_rates' => $character->growth_rates,
                'aptitudes' => $character->aptitudes,
                'base_stats' => $character->base_stats,
            ]);

            $this->dispatch('toast', [
                'type' => 'success',
                'message' => "Selected {$character->name} for plan creation",
            ]);
        }
    }

    public function render(): View
    {
        return view('livewire.characters.character-list', [
            'characters' => $this->characters,
            'teams' => $this->teams,
        ]);
    }
}
