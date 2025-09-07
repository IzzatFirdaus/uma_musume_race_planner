<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Umamusume>
 */
class UmamusumeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->unique()->slug,
            'name' => $this->faker->name,
            'nickname' => $this->faker->optional()->words(2, true),
            'team' => $this->faker->optional()->word,
            'release_batch' => $this->faker->optional()->word,
            'cv' => $this->faker->optional()->name,
            'birthday' => $this->faker->optional()->date('F d'),
            'height_cm' => $this->faker->optional()->numberBetween(140, 180),
            'weight' => $this->faker->optional()->word,
            'three_sizes' => [
                'bust' => $this->faker->numberBetween(70, 100),
                'waist' => $this->faker->numberBetween(50, 65),
                'hips' => $this->faker->numberBetween(70, 100),
            ],
            'images' => [
                'avatar' => null,
                'full' => null,
                'cropped' => null,
                'source' => null,
            ],
            'rarity' => $this->faker->numberBetween(1, 3),
            'growth_rates' => [
                'speed' => $this->faker->numberBetween(0, 20),
                'stamina' => $this->faker->numberBetween(0, 20),
                'power' => $this->faker->numberBetween(0, 20),
                'guts' => $this->faker->numberBetween(0, 20),
                'wisdom' => $this->faker->numberBetween(0, 20),
            ],
            'aptitudes' => [
                'terrain' => ['turf' => 'A', 'dirt' => 'G'],
                'distance' => ['sprint' => 'F', 'mile' => 'A', 'medium' => 'A', 'long' => 'A'],
                'strategy' => ['front' => 'A', 'leader' => 'A', 'chaser' => 'E', 'end' => 'G'],
            ],
            'base_stats' => [
                'speed' => $this->faker->numberBetween(50, 120),
                'stamina' => $this->faker->numberBetween(50, 120),
                'power' => $this->faker->numberBetween(50, 120),
                'guts' => $this->faker->numberBetween(50, 120),
                'wisdom' => $this->faker->numberBetween(50, 120),
            ],
            'unique_skill' => [
                'name' => $this->faker->optional()->words(2, true),
                'effect' => $this->faker->optional()->sentence,
            ],
            'skills' => [
                'initial' => [],
                'awakening' => [],
            ],
            'career_goals' => [],
            'tags' => [],
            'ui' => [
                'frame_color' => $this->faker->hexColor,
                'icon' => null,
                'pentagon_chart' => true,
            ],
            'links' => [
                'wiki' => null,
                'game8' => null,
            ],
        ];
    }
}
