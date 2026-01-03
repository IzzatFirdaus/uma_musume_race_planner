<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

/**
 * Activity Log Service Class
 *
 * Handles user-scoped activity logging and retrieval.
 * Implements FR-8.1, FR-8.6: Log user actions with user scoping.
 */
class ActivityLogService
{
    /**
     * Icon mappings for different actions.
     */
    private const ACTION_ICONS = [
        'create' => 'bi-plus-circle',
        'update' => 'bi-pencil',
        'delete' => 'bi-trash',
        'view' => 'bi-eye',
        'export' => 'bi-download',
        'import' => 'bi-upload',
        'snapshot' => 'bi-camera',
        'skill_add' => 'bi-star',
        'skill_remove' => 'bi-star-half',
        'convert' => 'bi-arrow-repeat',
        'default' => 'bi-info-circle',
    ];

    /**
     * Log an activity.
     *
     * @param array<string, mixed> $metadata
     */
    public function log(
        string $description,
        ?Model $subject = null,
        ?int $userId = null,
        string $action = 'default',
        array $metadata = []
    ): ActivityLog {
        $userId = $userId ?? Auth::id();
        $iconClass = self::ACTION_ICONS[$action] ?? self::ACTION_ICONS['default'];

        return ActivityLog::create([
            'user_id' => $userId,
            'description' => $description,
            'model_type' => $subject ? get_class($subject) : null,
            'model_id' => $subject?->getKey(),
            'metadata' => $metadata ?: null,
            'icon_class' => $iconClass,
        ]);
    }

    /**
     * Log a plan creation.
     */
    public function logPlanCreated(Plan $plan, ?int $userId = null): ActivityLog
    {
        return $this->log(
            "New plan created: {$plan->plan_title}",
            $plan,
            $userId,
            'create',
            ['plan_title' => $plan->plan_title]
        );
    }

    /**
     * Log a plan update.
     */
    public function logPlanUpdated(Plan $plan, ?int $userId = null, array $changes = []): ActivityLog
    {
        return $this->log(
            "Plan updated: {$plan->plan_title}",
            $plan,
            $userId,
            'update',
            ['plan_title' => $plan->plan_title, 'changes' => $changes]
        );
    }

    /**
     * Log a plan deletion.
     */
    public function logPlanDeleted(Plan $plan, ?int $userId = null): ActivityLog
    {
        return $this->log(
            "Plan deleted: {$plan->plan_title}",
            $plan,
            $userId,
            'delete',
            ['plan_title' => $plan->plan_title]
        );
    }

    /**
     * Log a snapshot creation.
     */
    public function logSnapshotCreated(Plan $plan, int $turnNumber, ?string $raceName = null, ?int $userId = null): ActivityLog
    {
        $description = $raceName
            ? "Snapshot created for {$plan->plan_title} at turn {$turnNumber} ({$raceName})"
            : "Snapshot created for {$plan->plan_title} at turn {$turnNumber}";

        return $this->log(
            $description,
            $plan,
            $userId,
            'snapshot',
            ['turn_number' => $turnNumber, 'race_name' => $raceName]
        );
    }

    /**
     * Log an export action.
     */
    public function logExport(string $format, int $count, ?int $userId = null): ActivityLog
    {
        return $this->log(
            "Exported {$count} plan(s) to {$format}",
            null,
            $userId,
            'export',
            ['format' => $format, 'count' => $count]
        );
    }

    /**
     * Log an import action.
     */
    public function logImport(string $format, int $count, ?int $userId = null): ActivityLog
    {
        return $this->log(
            "Imported {$count} plan(s) from {$format}",
            null,
            $userId,
            'import',
            ['format' => $format, 'count' => $count]
        );
    }

    /**
     * Log a local run conversion.
     */
    public function logConversion(Plan $plan, ?int $userId = null): ActivityLog
    {
        return $this->log(
            "Local run converted to account: {$plan->plan_title}",
            $plan,
            $userId,
            'convert',
            ['plan_title' => $plan->plan_title]
        );
    }

    /**
     * Get recent activity for the current user.
     * Implements FR-8.2: Display recent activity on dashboard (current user only when logged in).
     */
    public function getRecentForCurrentUser(int $limit = 10): Collection
    {
        $userId = Auth::id();

        return ActivityLog::forUser($userId)
            ->recent($limit)
            ->get();
    }

    /**
     * Get recent activity for a specific user.
     */
    public function getRecentForUser(?int $userId, int $limit = 10): Collection
    {
        return ActivityLog::forUser($userId)
            ->recent($limit)
            ->get();
    }

    /**
     * Get all activity (for admin or public view).
     */
    public function getRecent(int $limit = 10): Collection
    {
        return ActivityLog::recent($limit)->get();
    }

    /**
     * Get activity with pagination.
     */
    public function getPaginated(int $perPage = 20, ?int $userId = null): LengthAwarePaginator
    {
        $query = ActivityLog::orderBy('timestamp', 'desc');

        if ($userId !== null) {
            $query->forUser($userId);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get activity for a specific model.
     * Implements FR-8.4: Support activity log per career run view.
     */
    public function getForModel(Model $model, int $limit = 20): Collection
    {
        return ActivityLog::forModel(get_class($model), $model->getKey())
            ->recent($limit)
            ->get();
    }

    /**
     * Get activity for a plan.
     */
    public function getForPlan(Plan $plan, int $limit = 20): Collection
    {
        return $this->getForModel($plan, $limit);
    }

    /**
     * Get activity counts by action type.
     */
    public function getActivityCounts(?int $userId = null): array
    {
        $query = ActivityLog::query();

        if ($userId !== null) {
            $query->forUser($userId);
        }

        return [
            'total' => $query->count(),
            'today' => (clone $query)->whereDate('timestamp', today())->count(),
            'this_week' => (clone $query)->where('timestamp', '>=', now()->startOfWeek())->count(),
            'this_month' => (clone $query)->where('timestamp', '>=', now()->startOfMonth())->count(),
        ];
    }

    /**
     * Clear old activity logs (for maintenance).
     */
    public function clearOldLogs(int $daysToKeep = 90): int
    {
        return ActivityLog::where('timestamp', '<', now()->subDays($daysToKeep))->delete();
    }

    /**
     * Get activity grouped by date.
     */
    public function getGroupedByDate(?int $userId = null, int $days = 7): array
    {
        $query = ActivityLog::where('timestamp', '>=', now()->subDays($days))
            ->orderBy('timestamp', 'desc');

        if ($userId !== null) {
            $query->forUser($userId);
        }

        return $query->get()
            ->groupBy(fn(ActivityLog $log) => $log->timestamp->format('Y-m-d'))
            ->toArray();
    }
}
