<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\Skill;
use App\Models\SkillReference;
use Illuminate\Database\Eloquent\Factories\Factory;

class SkillFactory extends Factory
{
    protected $model = Skill::class;

    public function definition(): array
    {
        // Define realistic rarity probabilities (Normal > Rare > Unique)
        $rarity = $this->faker->randomElement([
            'Normal', 'Normal', 'Normal', 'Rare', 'Rare', 'Unique',
        ]);

        // Base SP cost depending on rarity
        $spCost = match ($rarity) {
            'Normal' => $this->faker->numberBetween(50, 150),
            'Rare' => $this->faker->numberBetween(150, 300),
            'Unique' => $this->faker->numberBetween(300, 500),
        };

        // Acquisition realistically favors 'no' (not yet learned)
        $acquired = $this->faker->randomElement(array_merge(
            array_fill(0, 8, 'no'),
            ['yes']
        ));

        // Tag skill type based on skill reference from the DB
        $skillRef = SkillReference::factory()->create();
        $statType = $skillRef->stat_type;  // e.g., 'Acceleration', 'Speed', 'Recovery', 'Debuff', etc.

        return [
            'plan_id' => Plan::factory(),
            'skill_reference_id' => $skillRef->id,
            'sp_cost' => $spCost,
            'acquired' => $acquired,
            'tag' => $statType, // Matches in-game categorization
            'notes' => $this->faker->optional(0.3)->sentence(),
            'rarity' => $rarity,
        ];
    }
}
