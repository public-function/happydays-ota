<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductOffer>
 */
class ProductOfferFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $offerNames = [
            3 => [
                '3 nights with breakfast & dinner',
                '3 nights city break',
                '3 nights romantic escape',
                '3 nights business stay',
            ],
            5 => [
                '5 nights breakfast only',
                '5 nights all-inclusive getaway',
                '5 nights weekend extended',
                '5 nights culture explorer',
            ],
            7 => [
                '7 nights summer holiday',
                '7 nights winter retreat',
                '7 nights wellness package',
                '7 nights family vacation',
            ],
            14 => [
                '14 nights long stay',
                '2 weeks all-inclusive',
                '14 nights explorer package',
            ],
        ];

        $duration = $this->faker->randomElement([3, 5, 7, 14]);
        $name = $this->faker->randomElement($offerNames[$duration]);

        // Realistic Danish hotel prices (DKK)
        $basePrice = match ($duration) {
            3 => $this->faker->numberBetween(3000, 6000),
            5 => $this->faker->numberBetween(5000, 10000),
            7 => $this->faker->numberBetween(7000, 14000),
            14 => $this->faker->numberBetween(12000, 25000),
            default => $this->faker->numberBetween(3000, 10000),
        };

        return [
            'name' => $name,
            'duration_nights' => $duration,
            'min_guests' => 1,
            'max_guests' => $this->faker->numberBetween(2, 4),
            'base_price' => $basePrice,
            'status' => 'active',
        ];
    }

    /**
     * Indicate that the offer is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the offer is draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    /**
     * Set a specific duration.
     */
    public function duration(int $nights): static
    {
        return $this->state(fn (array $attributes) => [
            'duration_nights' => $nights,
        ]);
    }
}
