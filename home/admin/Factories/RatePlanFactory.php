<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RatePlan>
 */
class RatePlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $ratePlans = [
            [
                'name' => 'Room Only',
                'board_type' => 'RO',
                'cancellation_policy' => [
                    'type' => 'free_cancellation',
                    'deadline_hours' => 24,
                    'description' => 'Free cancellation up to 24 hours before check-in',
                ],
            ],
            [
                'name' => 'Bed & Breakfast',
                'board_type' => 'BB',
                'cancellation_policy' => [
                    'type' => 'free_cancellation',
                    'deadline_hours' => 48,
                    'description' => 'Free cancellation up to 48 hours before check-in',
                ],
            ],
            [
                'name' => 'Half Board',
                'board_type' => 'HB',
                'cancellation_policy' => [
                    'type' => 'free_cancellation',
                    'deadline_hours' => 72,
                    'description' => 'Free cancellation up to 72 hours before check-in',
                ],
            ],
            [
                'name' => 'Full Board',
                'board_type' => 'FB',
                'cancellation_policy' => [
                    'type' => 'free_cancellation',
                    'deadline_hours' => 72,
                    'description' => 'Free cancellation up to 72 hours before check-in',
                ],
            ],
            [
                'name' => 'All Inclusive',
                'board_type' => 'AI',
                'cancellation_policy' => [
                    'type' => 'non_refundable',
                    'description' => 'Non-refundable rate',
                ],
            ],
            [
                'name' => 'Flexible Rate',
                'board_type' => 'RO',
                'cancellation_policy' => [
                    'type' => 'free_cancellation',
                    'deadline_hours' => 24,
                    'description' => 'Free cancellation until check-in',
                ],
            ],
        ];

        $ratePlan = $this->faker->randomElement($ratePlans);

        return [
            'name' => $ratePlan['name'],
            'board_type' => $ratePlan['board_type'],
            'cancellation_policy' => json_encode($ratePlan['cancellation_policy']),
            'status' => 'active',
        ];
    }

    /**
     * Indicate that the rate plan is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the rate plan is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}
