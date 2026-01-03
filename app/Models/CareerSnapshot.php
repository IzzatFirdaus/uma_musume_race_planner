<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Immutable race-day snapshot of career run state.
 * per requirements FR-12.1, FR-12.2, FR-12.4
 *
 * @property int $id
 * @property int $plan_id
 * @property int|null $race_prediction_id
 * @property int $turn_number
 * @property string|null $race_name
 * @property int $speed
 * @property int $stamina
 * @property int $power
 * @property int $guts
 * @property int $wit
 * @property int|null $total_sp_available
 * @property int|null $stamina_percentage
 * @property string|null $mood
 * @property string|null $conditions
 * @property array|null $skills_snapshot
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon $created_at
 * @property-read \App\Models\Plan $plan
 * @property-read \App\Models\RacePrediction|null $racePrediction
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CareerSnapshot newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CareerSnapshot newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CareerSnapshot query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CareerSnapshot wherePlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CareerSnapshot whereTurnNumber($value)
 *
 * @mixin \Eloquent
 */
class CareerSnapshot extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     * Snapshots are immutable - only created_at is used.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'plan_id',
        'race_prediction_id',
        'turn_number',
        'race_name',
        'speed',
        'stamina',
        'power',
        'guts',
        'wit',
        'total_sp_available',
        'stamina_percentage',
        'mood',
        'conditions',
        'skills_snapshot',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'skills_snapshot' => 'array',
        'turn_number' => 'integer',
        'speed' => 'integer',
        'stamina' => 'integer',
        'power' => 'integer',
        'guts' => 'integer',
        'wit' => 'integer',
        'total_sp_available' => 'integer',
        'stamina_percentage' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * Boot the model.
     * Snapshots are immutable - prevent updates.
     */
    protected static function booted(): void
    {
        static::updating(function (self $snapshot): bool {
            // Prevent updates to snapshots - they are immutable
            return false;
        });

        static::creating(function (self $snapshot): void {
            // Set created_at manually since we disabled timestamps
            $snapshot->setAttribute('created_at', now());
        });
    }

    /**
     * Get the plan that owns the snapshot.
     *
     * @return BelongsTo<\App\Models\Plan, \App\Models\CareerSnapshot>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Get the race prediction associated with this snapshot.
     *
     * @return BelongsTo<\App\Models\RacePrediction, \App\Models\CareerSnapshot>
     */
    public function racePrediction(): BelongsTo
    {
        return $this->belongsTo(RacePrediction::class);
    }

    /**
     * Get the total stats at snapshot time.
     */
    public function getTotalStatsAttribute(): int
    {
        return $this->speed + $this->stamina + $this->power + $this->guts + $this->wit;
    }

    /**
     * Create a snapshot from a plan's current state.
     */
    public static function createFromPlan(
        Plan $plan,
        int $turnNumber,
        ?string $raceName = null,
        ?RacePrediction $racePrediction = null,
        ?string $notes = null
    ): self {
        // Get current stats from the plan's latest turn or attributes
        $latestTurn = $plan->turns()->orderBy('turn_number', 'desc')->first();

        // Get acquired skills
        $acquiredSkills = $plan->skills()
            ->where('status', 'acquired')
            ->with('skillReference')
            ->get()
            ->map(fn($skill) => [
                'id' => $skill->skill_reference_id,
                'name' => $skill->skillReference?->name,
                'turn_acquired' => $skill->turn_acquired,
            ])
            ->toArray();

        return self::create([
            'plan_id' => $plan->id,
            'race_prediction_id' => $racePrediction?->id,
            'turn_number' => $turnNumber,
            'race_name' => $raceName ?? $racePrediction?->race_name,
            'speed' => $latestTurn?->speed ?? 0,
            'stamina' => $latestTurn?->stamina ?? 0,
            'power' => $latestTurn?->power ?? 0,
            'guts' => $latestTurn?->guts ?? 0,
            'wit' => $latestTurn?->wit ?? 0,
            'total_sp_available' => $plan->total_available_skill_points,
            'stamina_percentage' => $plan->stamina_percentage ?? $plan->energy,
            'mood' => $plan->mood?->name,
            'conditions' => $plan->condition?->name,
            'skills_snapshot' => $acquiredSkills,
            'notes' => $notes,
        ]);
    }
}
