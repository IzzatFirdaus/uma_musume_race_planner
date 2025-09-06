<?php

namespace App\Livewire;

use Livewire\Component;

class PlanDetails extends Component
{
    public function save()
    {
        // For now, just emit an event to let main.js handle the submission
        // This maintains compatibility with the existing JavaScript submission handler
        $this->dispatch('submitPlanForm', formId: 'planDetailsForm');
    }

    public function render()
    {
        return view('livewire.plan-details');
    }
}
