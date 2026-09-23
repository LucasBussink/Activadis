<?php

namespace Database\Factories;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Activity>
 */
class ActivityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'starts_at' => fake()->dateTimeBetween('+1 week', '+3 months'),
            'signup_deadline' => fake()->dateTimeBetween('now', '+1 week'),
            'cost' => fake()->randomFloat(2, 0, 100),
            'personal_contribution' => fake()->randomFloat(2, 0, 25),
            'includes_food' => fake()->boolean(),
            'food_description' => fake()->boolean() ? fake()->sentence() : null,
            'min_participants' => fake()->numberBetween(1, 5),
            'max_participants' => fake()->numberBetween(10, 50),
            'allow_partner' => fake()->boolean(),
            'is_external' => fake()->boolean(20),
            'status' => fake()->randomElement([Activity::STATUS_DRAFT, Activity::STATUS_PUBLISHED]),
        ];
    }
}
