<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class GuideStickyNav extends Component
{
    public array $sections = [
        ['id' => 'welcome', 'label' => 'Welcome'],
        ['id' => 'dashboard', 'label' => 'Dashboard'],
        ['id' => 'create-edit', 'label' => 'Plan Editor'],
        ['id' => 'ai-help', 'label' => 'AI Assistant'],
        ['id' => 'faq', 'label' => 'FAQ'],
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
