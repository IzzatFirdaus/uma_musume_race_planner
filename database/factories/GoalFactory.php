<?php

namespace Database\Factories;

use App\Models\Goal;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

class GoalFactory extends Factory
{
    protected $model = Goal::class;

    public function definition(): array
    {
        // Common race goal placements
        $placementGoals = ['1ST', 'TOP 3', 'TOP 5', 'TOP 10'];

        // Create several goal types
        $type = $this->faker->randomElement(['race_placement', 'fan_count', 'participate']);

        switch ($type) {
            case 'race_placement':
                // Random race name example
                $race = $this->faker->randomElement(['URA Final', 'Sapporo Sprint', 'Tokyo Derby', 'Fan Fest Special']);
                $placement = $this->faker->randomElement($placementGoals);
                $goalText = "Finish {$placement} in {$race}";
                $resultOptions = array_merge(['Pending', 'FAILED'], $placementGoals);
                break;

            case 'fan_count':
                $target = $this->faker->numberBetween(10000, 100000);
                $goalText = "Achieve at least {$target} fans";
                $resultOptions = ['Pending', 'FAILED', 'Achieved'];
                break;

            case 'participate':
                $race = $this->faker->randomElement(['Hakodate Stakes', 'Spring Invitation']);
                $goalText = "Participate in {$race}";
                $resultOptions = ['Pending', 'Not Started', 'Completed'];
                break;
        }

        return [
            'plan_id' => Plan::factory(),
            'goal' => $goalText,
            'result' => $this->faker->randomElement($resultOptions),
        ];
    }
}
