<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property int|null $user_id
 * @property \Illuminate\Support\Carbon $timestamp
 * @property string $description
 * @property string|null $model_type
 * @property int|null $model_id
 * @property array|null $metadata
 * @property string $icon_class
 * @property-read \App\Models\User|null $user
 * @property-read Model|null $subject
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereIconClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereTimestamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereModelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereModelId($value)
 *
 * @mixin \Eloquent
 */
class ActivityLog extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    public const CREATED_AT = 'timestamp';

    protected $table = 'activity_log';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'description',
        'model_type',
        'model_id',
        'metadata',
        'icon_class',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
        'timestamp' => 'datetime',
    ];

    /**
     * Get the user that performed the action.
     *
     * @return BelongsTo<\App\Models\User, \App\Models\ActivityLog>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subject model (polymorphic).
     *
     * @return MorphTo<Model, ActivityLog>
     */
    public function subject(): MorphTo
    {
        return $this->morphTo('model');
    }

    /**
     * Scope to filter by user.
     *
     * @param \Illuminate\Database\Eloquent\Builder<ActivityLog> $query
     * @return \Illuminate\Database\Eloquent\Builder<ActivityLog>
     */
    public function scopeForUser($query, ?int $userId)
    {
        if ($userId === null) {
            return $query->whereNull('user_id');
        }

        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by model type.
     *
     * @param \Illuminate\Database\Eloquent\Builder<ActivityLog> $query
     * @return \Illuminate\Database\Eloquent\Builder<ActivityLog>
     */
    public function scopeForModel($query, string $modelType, ?int $modelId = null)
    {
        $query->where('model_type', $modelType);

        if ($modelId !== null) {
            $query->where('model_id', $modelId);
        }

        return $query;
    }

    /**
     * Scope to get recent activity.
     *
     * @param \Illuminate\Database\Eloquent\Builder<ActivityLog> $query
     * @return \Illuminate\Database\Eloquent\Builder<ActivityLog>
     */
    public function scopeRecent($query, int $limit = 10)
    {
        return $query->orderBy('timestamp', 'desc')->limit($limit);
    }

    /**
     * Log an activity for a model.
     *
     * @param array<string, mixed> $metadata
     */
    public static function logActivity(
        string $description,
        ?Model $subject = null,
        ?int $userId = null,
        string $iconClass = 'bi-info-circle',
        array $metadata = []
    ): self {
        return self::create([
            'user_id' => $userId,
            'description' => $description,
            'model_type' => $subject ? get_class($subject) : null,
            'model_id' => $subject?->getKey(),
            'metadata' => $metadata ?: null,
            'icon_class' => $iconClass,
        ]);
    }
}
