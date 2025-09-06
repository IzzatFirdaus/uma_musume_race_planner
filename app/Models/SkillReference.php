<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $skill_name
 * @property string|null $description
 * @property string|null $stat_type
 * @property string|null $best_for
 * @property string|null $tag
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Skill> $skills
 * @property-read int|null $skills_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SkillReference newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SkillReference newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SkillReference query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SkillReference whereBestFor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SkillReference whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SkillReference whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SkillReference whereSkillName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SkillReference whereStatType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SkillReference whereTag($value)
 * @mixin \Eloquent
 */
class SkillReference extends Model
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
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'skill_reference';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'skill_name',
        'description',
        'stat_type',
        'best_for',
        'tag',
    ];

    /**
     * Get the skills that reference this definition.
     *
     * @return HasMany<Skill, SkillReference>
     */
    public function skills(): HasMany
    {
        return $this->hasMany(Skill::class);
    }
}
