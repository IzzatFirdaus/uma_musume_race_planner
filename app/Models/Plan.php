<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $plan_title
 * @property int|null $turn_before
 * @property string|null $race_name
 * @property string $name
 * @property string|null $career_stage
 * @property string|null $class
 * @property string|null $time_of_day
 * @property string|null $month
 * @property int|null $total_available_skill_points
 * @property string $acquire_skill
 * @property int|null $mood_id
 * @property int|null $condition_id
 * @property int|null $energy
 * @property string $race_day
 * @property string|null $goal
 * @property int|null $strategy_id
 * @property int $growth_rate_speed
 * @property int $growth_rate_stamina
 * @property int $growth_rate_power
 * @property int $growth_rate_guts
 * @property int $growth_rate_wit
 * @property string $status
 * @property string|null $source
 * @property string|null $trainee_image_path
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Attribute> $attributes
 * @property-read int|null $attributes_count
 * @property-read \App\Models\Condition|null $condition
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DistanceGrade> $distanceGrades
 * @property-read int|null $distance_grades_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Goal> $goals
 * @property-read int|null $goals_count
 * @property-read \App\Models\Mood|null $mood
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RacePrediction> $racePredictions
 * @property-read int|null $race_predictions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Skill> $skills
 * @property-read int|null $skills_count
 * @property-read \App\Models\Strategy|null $strategy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\StyleGrade> $styleGrades
 * @property-read int|null $style_grades_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TerrainGrade> $terrainGrades
 * @property-read int|null $terrain_grades_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Turn> $turns
 * @property-read int|null $turns_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereAcquireSkill($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereCareerStage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereConditionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereEnergy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereGoal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereGrowthRateGuts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereGrowthRatePower($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereGrowthRateSpeed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereGrowthRateStamina($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereGrowthRateWit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereMoodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan wherePlanTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereRaceDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereRaceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereStrategyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereTimeOfDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereTotalAvailableSkillPoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereTraineeImagePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereTurnBefore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan withoutTrashed()
 * @mixin \Eloquent
 */
class Plan extends Model
{
    // No custom factory, omit HasFactory generic
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'plan_title',
        'turn_before',
        'race_name',
        'name',
        'career_stage',
        'class',
        'time_of_day',
        'month',
        'total_available_skill_points',
        'acquire_skill',
        'mood_id',
        'condition_id',
        'energy',
        'race_day',
        'goal',
        'strategy_id',
        'growth_rate_speed',
        'growth_rate_stamina',
        'growth_rate_power',
        'growth_rate_guts',
        'growth_rate_wit',
        'status',
        'source',
        'trainee_image_path',
    ];

    /**
     * Get the attributes for the plan.
     *
     * @return HasMany<Attribute, Plan>
     */
    public function attributes(): HasMany
    {
        return $this->hasMany(Attribute::class);
    }

    /**
     * Get the skills for the plan.
     *
     * @return HasMany<Skill, Plan>
     */
    public function skills(): HasMany
    {
        return $this->hasMany(Skill::class);
    }

    /**
     * Get the goals for the plan.
     *
     * @return HasMany<Goal, Plan>
     */
    public function goals(): HasMany
    {
        return $this->hasMany(Goal::class);
    }

    /**
     * Get the race predictions for the plan.
     *
     * @return HasMany<RacePrediction, Plan>
     */
    public function racePredictions(): HasMany
    {
        return $this->hasMany(RacePrediction::class);
    }

    /**
     * Get the turns for the plan.
     *
     * @return HasMany<Turn, Plan>
     */
    public function turns(): HasMany
    {
        return $this->hasMany(Turn::class);
    }

    /**
     * Get the terrain grades for the plan.
     *
     * @return HasMany<TerrainGrade, Plan>
     */
    public function terrainGrades(): HasMany
    {
        return $this->hasMany(TerrainGrade::class);
    }

    /**
     * Get the distance grades for the plan.
     *
     * @return HasMany<DistanceGrade, Plan>
     */
    public function distanceGrades(): HasMany
    {
        return $this->hasMany(DistanceGrade::class);
    }

    /**
     * Get the style grades for the plan.
     *
     * @return HasMany<StyleGrade, Plan>
     */
    public function styleGrades(): HasMany
    {
        return $this->hasMany(StyleGrade::class);
    }

    /**
     * Get the mood associated with the plan.
     *
     * @return BelongsTo<Mood, Plan>
     */
    public function mood(): BelongsTo
    {
        return $this->belongsTo(Mood::class);
    }

    /**
     * Get the condition associated with the plan.
     *
     * @return BelongsTo<Condition, Plan>
     */
    public function condition(): BelongsTo
    {
        return $this->belongsTo(Condition::class);
    }

    /**
     * Get the strategy associated with the plan.
     *
     * @return BelongsTo<Strategy, Plan>
     */
    public function strategy(): BelongsTo
    {
        return $this->belongsTo(Strategy::class);
    }
}
