<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $name
 * @property string|null $nickname
 * @property string|null $team
 * @property string|null $release_batch
 * @property string|null $cv
 * @property string|null $birthday
 * @property int|null $height_cm
 * @property string|null $weight
 * @property array|null $three_sizes
 * @property array|null $images
 * @property int $rarity
 * @property array|null $growth_rates
 * @property array|null $aptitudes
 * @property array|null $base_stats
 * @property array|null $unique_skill
 * @property array|null $skills
 * @property array|null $career_goals
 * @property array|null $tags
 * @property array|null $ui
 * @property array|null $links
 */
class Umamusume extends Model
{
    public $incrementing = false;

    protected $table = 'umamusume';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    protected $fillable = [
        'id', 'name', 'nickname', 'team', 'release_batch', 'cv', 'birthday', 'height_cm', 'weight',
        'three_sizes', 'images', 'rarity', 'growth_rates', 'aptitudes', 'base_stats', 'unique_skill',
        'skills', 'career_goals', 'tags', 'ui', 'links',
    ];

    protected function casts(): array
    {
        return [
            'three_sizes' => AsArrayObject::class,
            'images' => AsArrayObject::class,
            'growth_rates' => AsArrayObject::class,
            'aptitudes' => AsArrayObject::class,
            'base_stats' => AsArrayObject::class,
            'unique_skill' => AsArrayObject::class,
            'skills' => AsArrayObject::class,
            'career_goals' => AsArrayObject::class,
            'tags' => AsArrayObject::class,
            'ui' => AsArrayObject::class,
            'links' => AsArrayObject::class,
        ];
    }
}
