<?php

namespace Database\Factories;

use App\Models\Attribute;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttributeFactory extends Factory
{
    protected $model = Attribute::class;

    public function definition(): array
    {
        // Pick which attribute this record is for
        $attributeName = $this->faker->randomElement(['SPEED', 'STAMINA', 'POWER', 'GUTS', 'WIT']);

        // Determine typical race distance strategy (affects stat scaling)
        $distance = $this->faker->randomElement(['SPRINT', 'MEDIUM', 'LONG']);

        // Base values
        $speed = $this->faker->numberBetween(700, 1200);
        $stamina = match ($distance) {
            'SPRINT' => $this->faker->numberBetween(400, 800),
            'MEDIUM' => $this->faker->numberBetween(700, 1000),
            'LONG' => $this->faker->numberBetween(900, 1300),
        };
        $power = $this->faker->numberBetween(400, 900);
        $guts = $this->faker->numberBetween(0, 400);
        $wit = $this->faker->numberBetween(200, 800);

        // Pick value based on chosen attribute
        $value = match ($attributeName) {
            'SPEED' => $speed,
            'STAMINA' => $stamina,
            'POWER' => $power,
            'GUTS' => $guts,
            'WIT' => $wit,
        };

        // Convert numeric value into a grade
        $grade = match (true) {
            $value >= 1100 => 'S',
            $value >= 900 => 'A',
            $value >= 700 => 'B',
            $value >= 500 => 'C',
            $value >= 300 => 'D',
            $value >= 100 => 'E',
            default => 'F',
        };

        return [
            'plan_id' => Plan::factory(),
            'attribute_name' => $attributeName,
            'value' => $value,
            'grade' => $grade,
        ];
    }
}
