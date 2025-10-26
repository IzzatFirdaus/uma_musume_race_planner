<?php

namespace App\Livewire\Layout;

use Livewire\Attributes\On;
use Livewire\Component;

class Modals extends Component
{
    public array $messageModal = [];

    public array $confirmationModal = [];

    public array $loadingModal = [];

    // Event listeners converted to attribute-based handlers (Livewire #[On])

    public function mount()
    {
        $this->resetModals();
    }

    #[On('showMessageModal')]
    public function showMessageModal($title, $message, $icon = 'bi-info-circle', $actions = null)
    {
        $this->messageModal = [
            'title' => $title,
            'message' => $message,
            'icon' => $icon,
            'actions' => $actions ?? [[
                'text' => 'OK',
                'class' => 'btn-primary',
                'action' => 'closeMessageModal',
            ]],
        ];

        $this->dispatch('show-message-modal');
    }

    #[On('showConfirmationModal')]
    public function showConfirmationModal($title, $message, $confirmText = 'Confirm', $action = null, $details = null)
    {
        $this->confirmationModal = [
            'title' => $title,
            'message' => $message,
            'confirmText' => $confirmText,
            'action' => $action,
            'details' => $details,
        ];

        $this->dispatch('show-confirmation-modal');
    }

    #[On('showLoadingModal')]
    public function showLoadingModal($message = 'Loading...', $details = null)
    {
        $this->loadingModal = [
            'message' => $message,
            'details' => $details,
        ];

        $this->dispatch('show-loading-modal');
    }

    public function closeMessageModal()
    {
        $this->messageModal = [];
        $this->dispatch('close-message-modal');
    }

    public function closeConfirmationModal()
    {
        $this->confirmationModal = [];
        $this->dispatch('close-confirmation-modal');
    }

    public function closeLoadingModal()
    {
        $this->loadingModal = [];
        $this->dispatch('close-loading-modal');
    }

    public function confirmAction()
    {
        $action = $this->confirmationModal['action'] ?? null;

        if ($action) {
            // Dispatch the confirmed action
            $this->dispatch($action);
        }

        $this->closeConfirmationModal();
    }

    #[On('closeAllModals')]
    public function closeAllModals()
    {
        $this->closeMessageModal();
        $this->closeConfirmationModal();
        $this->closeLoadingModal();
    }

    private function resetModals()
    {
        $this->messageModal = [];
        $this->confirmationModal = [];
        $this->loadingModal = [];
    }

    public function render()
    {
        return view('livewire.layout.modals');
    }
}
