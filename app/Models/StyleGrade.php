<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $plan_id
 * @property string $style
 * @property string|null $grade
 * @property-read \App\Models\Plan $plan
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StyleGrade newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StyleGrade newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StyleGrade query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StyleGrade whereGrade($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StyleGrade whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StyleGrade wherePlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StyleGrade whereStyle($value)
 * @mixin \Eloquent
 */
class StyleGrade extends Model
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
        'style',
        'grade',
    ];

    /**
     * Get the plan that owns the grade.
     *
     * @return BelongsTo<Plan, StyleGrade>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
