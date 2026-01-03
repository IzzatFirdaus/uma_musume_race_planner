<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Enums\StorageMode;
use App\Models\ActivityLog;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Recent Activity Dashboard Component
 *
 * Displays user-scoped activity for account runs and local activity
 * from browser storage. Implements FR-8.2, FR-8.7.
 */
class RecentActivity extends Component
{
    public int $limit = 10;

    public int $page = 1;

    public bool $hasMore = false;

    /**
     * Get activities from database (account runs) for current user.
     *
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function activities(): array
    {
        $userId = Auth::id();

        $query = ActivityLog::query()
            ->orderBy('timestamp', 'desc')
            ->limit($this->limit * $this->page);

        // User-scoped: only show current user's activity when logged in
        if ($userId !== null) {
            $query->where('user_id', $userId);
        } else {
            // For anonymous users, show only null user_id activities (public/guest)
            $query->whereNull('user_id');
        }

        $activities = $query->get();

        // Check if there are more activities
        $totalCount = ActivityLog::query()
            ->when($userId !== null, fn($q) => $q->where('user_id', $userId))
            ->when($userId === null, fn($q) => $q->whereNull('user_id'))
            ->count();

        $this->hasMore = $totalCount > ($this->limit * $this->page);

        return $activities->map(function (ActivityLog $log) {
            // Determine source based on metadata or model
            $source = 'account';
            if (isset($log->metadata['storage_mode'])) {
                $source = $log->metadata['storage_mode'];
            } elseif ($log->model_type === 'App\\Models\\Plan' && $log->model_id) {
                $plan = \App\Models\Plan::find($log->model_id);
                if ($plan && $plan->storage_mode === StorageMode::Local) {
                    $source = 'local';
                }
            }

            return [
                'id' => $log->id,
                'description' => $log->description,
                'timestamp' => $log->timestamp->toIso8601String(),
                'icon_class' => $log->icon_class ?? 'bi-info-circle',
                'plan_name' => $log->metadata['plan_title'] ?? null,
                'source' => $source,
                'source_label' => $source === 'local' ? 'Local' : 'Account',
                'source_badge_class' => $source === 'local'
                    ? 'bg-warning text-dark'
                    : 'bg-primary text-white',
            ];
        })->toArray();
    }

    /**
     * Load more activities.
     */
    public function loadMore(): void
    {
        $this->page++;
    }

    /**
     * Refresh activities.
     */
    public function refresh(): void
    {
        $this->page = 1;
        unset($this->activities);
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.dashboard.recent-activity', [
            'activities' => $this->activities,
            'hasMore' => $this->hasMore,
        ]);
    }
}
