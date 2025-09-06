<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $plan_id
 * @property string|null $race_name
 * @property string|null $venue
 * @property string|null $ground
 * @property string|null $distance
 * @property string|null $track_condition
 * @property string|null $direction
 * @property string $speed
 * @property string $stamina
 * @property string $power
 * @property string $guts
 * @property string $wit
 * @property string|null $comment
 * @property-read \App\Models\Plan $plan
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RacePrediction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RacePrediction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RacePrediction query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RacePrediction whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RacePrediction whereDirection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RacePrediction whereDistance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RacePrediction whereGround($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RacePrediction whereGuts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RacePrediction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RacePrediction wherePlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RacePrediction wherePower($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RacePrediction whereRaceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RacePrediction whereSpeed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RacePrediction whereStamina($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RacePrediction whereTrackCondition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RacePrediction whereVenue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RacePrediction whereWit($value)
 * @mixin \Eloquent
 */
class RacePrediction extends Model
{
    // No custom factory, omit HasFactory generic
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'plan_id',
        'race_name',
        'venue',
        'ground',
        'distance',
        'track_condition',
        'direction',
        'speed',
        'stamina',
        'power',
        'guts',
        'wit',
        'comment',
    ];

    /**
     * Get the plan that owns the race prediction.
     *
     * @return BelongsTo<Plan, RacePrediction>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
