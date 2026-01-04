<?php

declare(strict_types=1);

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class GuideStickyNav extends Component
{
    public array $sections = [
        ['id' => 'welcome', 'label' => 'Welcome'],
        ['id' => 'dashboard', 'label' => 'Dashboard'],
        ['id' => 'plan-editor', 'label' => 'Plan Editor'],
        ['id' => 'skills', 'label' => 'Skills'],
        ['id' => 'races', 'label' => 'Races'],
        ['id' => 'goals', 'label' => 'Goals'],
        ['id' => 'export-import', 'label' => 'Export/Import'],
        ['id' => 'local-data', 'label' => 'Local Data'],
        ['id' => 'characters', 'label' => 'Characters'],
        ['id' => 'keyboard', 'label' => 'Shortcuts'],
        ['id' => 'glossary', 'label' => 'Glossary'],
    ];

    public ?string $active = null;

    #[On('guide:sectionChanged')]
    public function setActive(?string $id): void
    {
        $this->active = $id;
    }

    public function render()
    {
        return view('livewire.guide-sticky-nav', [
            'sections' => $this->sections,
            'active' => $this->active,
        ]);
    }
}
