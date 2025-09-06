<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $plan_id
 * @property int $skill_reference_id
 * @property string|null $sp_cost
 * @property string $acquired
 * @property string|null $tag
 * @property string|null $notes
 * @property-read \App\Models\Plan $plan
 * @property-read \App\Models\SkillReference $skillReference
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Skill newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Skill newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Skill query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Skill whereAcquired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Skill whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Skill whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Skill wherePlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Skill whereSkillReferenceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Skill whereSpCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Skill whereTag($value)
 * @mixin \Eloquent
 */
class Skill extends Model
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
        'skill_reference_id',
        'sp_cost',
        'acquired',
        'tag',
        'notes',
    ];

    /**
     * Get the plan that owns the skill.
     *
     * @return BelongsTo<Plan, Skill>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Get the skill reference definition.
     *
     * @return BelongsTo<SkillReference, Skill>
     */
    public function skillReference(): BelongsTo
    {
        return $this->belongsTo(SkillReference::class);
    }
}
