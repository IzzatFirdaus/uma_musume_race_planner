<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $plan_id
 * @property int $turn_number
 * @property int $speed
 * @property int $stamina
 * @property int $power
 * @property int $guts
 * @property int $wit
 * @property-read \App\Models\Plan $plan
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Turn newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Turn newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Turn query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Turn whereGuts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Turn whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Turn wherePlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Turn wherePower($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Turn whereSpeed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Turn whereStamina($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Turn whereTurnNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Turn whereWit($value)
 *
 * @mixin \Eloquent
 */
class Turn extends Model
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
        'turn_number',
        'speed',
        'stamina',
        'power',
        'guts',
        'wit',
    ];

    /**
     * Get the plan that owns the turn.
     *
     * @return BelongsTo<\App\Models\Plan, \App\Models\Turn>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
