<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Skill extends Model
    /**
     * @property int $sp_cost
     * @property string $acquired
     * @property string $notes
     * @property SkillReference $skillReference
     */
{
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
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Get the skill reference definition.
     */
    public function skillReference(): BelongsTo
    {
        return $this->belongsTo(SkillReference::class);
    }
}
