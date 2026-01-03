<?php

declare(strict_types=1);

namespace App\Livewire\Skills;

use App\Models\SkillReference;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Skill Search Autocomplete Component
 *
 * Accessible autocomplete for skill search with EN+JP matching.
 * Implements FR-4.4, FR-4B.1, FR-4B.3, FR-4B.5.
 */
class SkillSearch extends Component
{
    public string $query = '';

    public bool $showDropdown = false;

    public int $highlightedIndex = -1;

    public ?int $planId = null;

    public function mount(?int $planId = null): void
    {
        $this->planId = $planId;
    }

    /**
     * Search skills with debounced query.
     * Implements FR-4B.6: 300ms debounce.
     */
    public function updatedQuery(): void
    {
        $this->highlightedIndex = -1;

        if (strlen($this->query) >= 2) {
            $this->showDropdown = true;
        } else {
            $this->showDropdown = false;
        }
    }

    /**
     * Get search results.
     * Implements FR-4B.1: < 200ms response, FR-4B.3: EN+JP matching.
     *
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function results(): array
    {
        if (strlen($this->query) < 2) {
            return [];
        }

        $cacheKey = 'skill_search_' . md5($this->query);

        return Cache::remember($cacheKey, 300, function () {
            return SkillReference::query()
                ->where(function ($q) {
                    $q->where('name', 'like', "%{$this->query}%")
                        ->orWhere('name_jp', 'like', "%{$this->query}%")
                        ->orWhere('description', 'like', "%{$this->query}%");
                })
                ->orderBy('name')
                ->limit(10)
                ->get()
                ->map(fn($skill) => [
                    'id' => $skill->id,
                    'name' => $skill->name,
                    'name_jp' => $skill->name_jp,
                    'type' => $skill->type,
                    'sp_cost' => $skill->sp_cost,
                    'description' => $skill->description,
                ])
                ->toArray();
        });
    }

    /**
     * Handle keyboard navigation.
     * Implements FR-4B.4: ↑/↓ to navigate, Enter to select, Esc to close.
     */
    public function handleKeydown(string $key): void
    {
        $resultsCount = count($this->results);

        switch ($key) {
            case 'ArrowDown':
                $this->highlightedIndex = min($this->highlightedIndex + 1, $resultsCount - 1);
                break;
            case 'ArrowUp':
                $this->highlightedIndex = max($this->highlightedIndex - 1, -1);
                break;
            case 'Enter':
                if ($this->highlightedIndex >= 0 && isset($this->results[$this->highlightedIndex])) {
                    $this->selectSkill($this->results[$this->highlightedIndex]['id']);
                }
                break;
            case 'Escape':
                $this->closeDropdown();
                break;
        }
    }

    /**
     * Select a skill from the dropdown.
     */
    public function selectSkill(int $skillId): void
    {
        $skill = collect($this->results)->firstWhere('id', $skillId);

        if ($skill) {
            $this->dispatch('skill-selected', skill: $skill, planId: $this->planId);
            $this->query = '';
            $this->closeDropdown();
        }
    }

    /**
     * Close the dropdown.
     */
    public function closeDropdown(): void
    {
        $this->showDropdown = false;
        $this->highlightedIndex = -1;
    }

    /**
     * Open the dropdown.
     */
    public function openDropdown(): void
    {
        if (strlen($this->query) >= 2) {
            $this->showDropdown = true;
        }
    }

    public function render()
    {
        return view('livewire.skills.skill-search');
    }
}
