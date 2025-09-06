<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $plan_id
 * @property string $distance
 * @property string|null $grade
 * @property-read \App\Models\Plan $plan
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DistanceGrade newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DistanceGrade newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DistanceGrade query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DistanceGrade whereDistance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DistanceGrade whereGrade($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DistanceGrade whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DistanceGrade wherePlanId($value)
 *
 * @mixin \Eloquent
 */
class DistanceGrade extends Model
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
        'distance',
        'grade',
    ];

    /**
     * Get the plan that owns the grade.
     *
     * @return BelongsTo<\App\Models\Plan, \App\Models\DistanceGrade>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
