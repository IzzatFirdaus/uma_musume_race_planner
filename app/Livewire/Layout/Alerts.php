<?php

namespace App\Livewire\Layout;

use Livewire\Attributes\On;
use Livewire\Component;

class Alerts extends Component
{
    public array $alerts = [];

    // Converted to attribute-based listeners (#[On])

    public function mount()
    {
        $this->alerts = [];

        // Load flash messages from session
        $this->loadSessionMessages();
    }

    #[On('showAlert')]
    public function showAlert($type, $message, $title = null, $timeout = 5000, $persistent = false)
    {
        $alert = [
            'id' => uniqid(),
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'timeout' => $persistent ? 0 : $timeout,
            'persistent' => $persistent,
            'timestamp' => now()->timestamp,
        ];

        $this->alerts[] = $alert;

        // Limit to 5 alerts maximum
        if (count($this->alerts) > 5) {
            array_shift($this->alerts);
        }
    }

    #[On('showSuccess')]
    public function showSuccess($message, $title = 'Success', $timeout = 4000)
    {
        $this->showAlert('success', $message, $title, $timeout);
    }

    #[On('showError')]
    public function showError($message, $title = 'Error', $timeout = 6000, $persistent = false)
    {
        $this->showAlert('error', $message, $title, $timeout, $persistent);
    }

    #[On('showWarning')]
    public function showWarning($message, $title = 'Warning', $timeout = 5000)
    {
        $this->showAlert('warning', $message, $title, $timeout);
    }

    #[On('showInfo')]
    public function showInfo($message, $title = 'Information', $timeout = 4000)
    {
        $this->showAlert('info', $message, $title, $timeout);
    }

    public function dismissAlert($alertId)
    {
        $this->alerts = array_filter($this->alerts, function ($alert) use ($alertId) {
            return $alert['id'] !== $alertId;
        });

        // Re-index array
        $this->alerts = array_values($this->alerts);
    }

    #[On('clearAlerts')]
    public function clearAlerts()
    {
        $this->alerts = [];
    }

    private function loadSessionMessages()
    {
        // Load success messages
        if (session()->has('success')) {
            $this->showSuccess(session('success'));
        }

        // Load error messages
        if (session()->has('error')) {
            $this->showError(session('error'));
        }

        // Load warning messages
        if (session()->has('warning')) {
            $this->showWarning(session('warning'));
        }

        // Load info messages
        if (session()->has('info')) {
            $this->showInfo(session('info'));
        }

        // Load validation errors
        if (session()->has('errors')) {
            $errors = session('errors');
            foreach ($errors->all() as $error) {
                $this->showError($error, 'Validation Error');
            }
        }
    }

    public function render()
    {
        return view('livewire.layout.alerts');
    }
}
