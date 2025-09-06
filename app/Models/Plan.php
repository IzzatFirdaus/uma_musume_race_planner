<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
    /**
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Attribute> $attributes
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Skill> $skills
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Goal> $goals
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RacePrediction> $racePredictions
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Turn> $turns
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TerrainGrade> $terrainGrades
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DistanceGrade> $distanceGrades
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\StyleGrade> $styleGrades
     * @property-read \App\Models\Mood $mood
     * @property-read \App\Models\Condition $condition
     * @property-read \App\Models\Strategy $strategy
     */
{
    use HasFactory, SoftDeletes;

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
     */
    public function mood(): BelongsTo
    {
        return $this->belongsTo(Mood::class);
    }

    /**
     * Get the condition associated with the plan.
     */
    public function condition(): BelongsTo
    {
        return $this->belongsTo(Condition::class);
    }

    /**
     * Get the strategy associated with the plan.
     */
    public function strategy(): BelongsTo
    {
        return $this->belongsTo(Strategy::class);
    }
}
