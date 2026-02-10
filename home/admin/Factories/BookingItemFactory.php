<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BookingItem>
 */
class BookingItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nights = $this->faker->numberBetween(1, 14);
        $checkInDate = Carbon::now()->addDays($this->faker->numberBetween(1, 60))->toDateString();
        $quantity = $this->faker->numberBetween(1, 3);
        $unitPrice = $this->faker->numberBetween(500, 2000);
        $totalPrice = $unitPrice * $nights * $quantity;

        // 85% confirmed, 10% pending, 5% cancelled
        $statusRoll = $this->faker->numberBetween(1, 100);
        $status = match (true) {
            $statusRoll <= 85 => 'confirmed',
            $statusRoll <= 95 => 'pending',
            default => 'cancelled',
        };

        return [
            'check_in_date' => $checkInDate,
            'nights' => $nights,
            'quantity' => $quantity,
            'adults' => $this->faker->numberBetween(1, 4),
            'children' => $this->faker->numberBetween(0, 2),
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice,
            'status' => $status,
            'snapshot' => null,
        ];
    }

    /**
     * Indicate that the item is confirmed.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
        ]);
    }

    /**
     * Indicate that the item is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate that the item is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    /**
     * Set specific check-in date.
     */
    public function checkInDate(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'check_in_date' => $date,
        ]);
    }

    /**
     * Set number of nights.
     */
    public function nights(int $nights): static
    {
        return $this->state(fn (array $attributes) => [
            'nights' => $nights,
            'total_price' => ($attributes['unit_price'] ?? $this->faker->numberBetween(500, 2000)) * $nights * ($attributes['quantity'] ?? 1),
        ]);
    }
}
