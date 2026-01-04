<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SkillStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $plan_id
 * @property int $skill_reference_id
 * @property string|null $sp_cost
 * @property string $acquired
 * @property SkillStatus $status
 * @property int|null $turn_acquired
 * @property string|null $tag
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Plan $plan
 * @property-read \App\Models\SkillReference $skillReference
 *
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Skill whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Skill whereTurnAcquired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Skill onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Skill withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Skill withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Skill extends Model
{
    use HasFactory;
    use SoftDeletes;

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
        'status',
        'turn_acquired',
        'tag',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => SkillStatus::class,
        'turn_acquired' => 'integer',
    ];

    /**
     * Get the plan that owns the skill.
     *
     * @return BelongsTo<\App\Models\Plan, \App\Models\Skill>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Get the skill reference definition.
     *
     * @return BelongsTo<\App\Models\SkillReference, \App\Models\Skill>
     */
    public function skillReference(): BelongsTo
    {
        return $this->belongsTo(SkillReference::class);
    }

    /**
     * Check if this skill is acquired.
     */
    public function isAcquired(): bool
    {
        return $this->status === SkillStatus::Acquired;
    }

    /**
     * Check if this skill is skipped.
     */
    public function isSkipped(): bool
    {
        return $this->status === SkillStatus::Skipped;
    }

    /**
     * Check if this skill is suggested.
     */
    public function isSuggested(): bool
    {
        return $this->status === SkillStatus::Suggested;
    }

    /**
     * Scope to filter by status.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Skill>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Skill>
     */
    public function scopeStatus($query, SkillStatus $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter acquired skills only.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Skill>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Skill>
     */
    public function scopeAcquired($query)
    {
        return $query->where('status', SkillStatus::Acquired);
    }

    /**
     * Scope to filter suggested skills only.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Skill>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Skill>
     */
    public function scopeSuggested($query)
    {
        return $query->where('status', SkillStatus::Suggested);
    }

    /**
     * Get the SP cost as an integer.
     * Implements Requirements 6.7: SP calculation support.
     */
    public function getSpCostInt(): int
    {
        return (int) ($this->sp_cost ?? 0);
    }

    /**
     * Validate that turn_acquired is set when status is Acquired.
     * Implements Requirements 6.5: turn_acquired required when status=acquired.
     *
     * @throws \InvalidArgumentException
     */
    public function validateTurnAcquired(): void
    {
        if ($this->status === SkillStatus::Acquired && $this->turn_acquired === null) {
            throw new \InvalidArgumentException(
                'turn_acquired is required when skill status is Acquired'
            );
        }
    }

    /**
     * Check if turn_acquired is valid (1-78 range).
     * Implements Requirements 6.5: Valid turn range validation.
     */
    public function hasTurnAcquiredInRange(): bool
    {
        if ($this->turn_acquired === null) {
            return true; // null is valid for non-acquired skills
        }

        return $this->turn_acquired >= 1 && $this->turn_acquired <= 78;
    }

    /**
     * Boot method for model events.
     */
    protected static function booted(): void
    {
        // Validate turn_acquired on save
        static::saving(function (self $skill): void {
            // Clear turn_acquired if status is not Acquired
            if ($skill->status !== SkillStatus::Acquired) {
                $skill->turn_acquired = null;
            }

            // Validate turn_acquired range if set
            if ($skill->turn_acquired !== null) {
                if ($skill->turn_acquired < 1 || $skill->turn_acquired > 78) {
                    throw new \InvalidArgumentException(
                        'turn_acquired must be between 1 and 78'
                    );
                }
            }
        });
    }
}
