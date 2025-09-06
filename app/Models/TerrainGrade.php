<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $plan_id
 * @property string $terrain
 * @property string|null $grade
 * @property-read \App\Models\Plan $plan
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TerrainGrade newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TerrainGrade newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TerrainGrade query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TerrainGrade whereGrade($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TerrainGrade whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TerrainGrade wherePlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TerrainGrade whereTerrain($value)
 *
 * @mixin \Eloquent
 */
class TerrainGrade extends Model
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
        'terrain',
        'grade',
    ];

    /**
     * Get the plan that owns the grade.
     *
     * @return BelongsTo<\App\Models\Plan, \App\Models\TerrainGrade>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
