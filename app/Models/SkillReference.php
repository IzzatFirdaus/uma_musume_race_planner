<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SkillReference extends Model
    /**
     * @property string $skill_name
     */
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'skill_reference';

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
        'skill_name',
        'description',
        'stat_type',
        'best_for',
        'tag',
    ];

    /**
     * Get the skills that reference this definition.
     */
    public function skills(): HasMany
    {
        return $this->hasMany(Skill::class);
    }
}
