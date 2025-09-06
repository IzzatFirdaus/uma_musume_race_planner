<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class PlanInlineDetails extends Component
{
    public function save()
    {
        // For now, just emit an event to let main.js handle the submission
        // This maintains compatibility with the existing JavaScript submission handler
        $this->dispatch('submitPlanForm', formId: 'planDetailsFormInline');
    }

    public function render()
    {
        return view('livewire.dashboard.plan-inline-details');
    }
}
