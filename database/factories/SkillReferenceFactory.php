<?php

namespace Database\Factories;

use App\Models\SkillReference;
use Illuminate\Database\Eloquent\Factories\Factory;

class SkillReferenceFactory extends Factory
{
    protected $model = SkillReference::class;

    public function definition(): array
    {
        // Define valid stat types based on the game
        $statTypes = ['Speed', 'Acceleration', 'Recovery', 'Passive', 'Debuff', 'Starting Gate', 'Lane Change', 'Observation'];

        // Fake 'best_for' scenarios
        $bestFors = [
            'Front-runner builds',
            'Pack runners',
            'Late surger builds',
            'Pace chaser builds',
            'All runners',
            'Competitive PvP',
            'Long-distance specialists',
            'Corner-heavy tracks',
        ];

        // Tag grouping - max 5 characters to fit column constraint
        $tagMapping = [
            'Speed' => 'spd',
            'Acceleration' => 'acc',
            'Recovery' => 'rec',
            'Passive' => 'pas',
            'Debuff' => 'deb',
            'Starting Gate' => 'gate',
            'Lane Change' => 'lane',
            'Observation' => 'obs',
        ];

        return [
            'skill_name' => ucfirst($this->faker->unique()->words(2, true)),
            'description' => $this->faker->sentence(),
            'stat_type' => $stat = $this->faker->randomElement($statTypes),
            'best_for' => $this->faker->randomElement($bestFors),
            'tag' => $tagMapping[$stat],
        ];
    }
}
