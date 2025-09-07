<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Condition>
 */
class ConditionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'label' => fake()->randomElement([
                'MIGRAINE',
                'DRY SKIN',
                'INSOMNIA',
                'SLOW METABOLISM',
                'SLACKER',
                'UNDER THE WEATHER',
                'SPRING BUD',
                'SUSPICIOUS CLOUDS',
            ]),
        ];
    }
}
