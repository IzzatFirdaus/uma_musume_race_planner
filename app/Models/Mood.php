<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $label
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Plan> $plans
 * @property-read int|null $plans_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mood newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mood newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mood query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mood whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mood whereLabel($value)
 * @mixin \Eloquent
 */
class Mood extends Model
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
    protected $fillable = ['label'];

    /**
     * Get the plans for the mood.
     *
     * @return HasMany<Plan, Mood>
     */
    public function plans(): HasMany
    {
        return $this->hasMany(Plan::class);
    }
}
