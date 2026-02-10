<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate a unique booking reference
        $reference = 'HD' . strtoupper(Str::random(6));
        
        // 80% confirmed, 15% pending, 5% cancelled
        $statusRoll = $this->faker->numberBetween(1, 100);
        $status = match (true) {
            $statusRoll <= 80 => 'confirmed',
            $statusRoll <= 95 => 'pending',
            default => 'cancelled',
        };

        $currencies = ['EUR', 'DKK', 'USD', 'GBP'];
        $currency = $this->faker->randomElement($currencies);

        return [
            'reference' => $reference,
            'customer_name' => $this->faker->name(),
            'customer_email' => $this->faker->safeEmail(),
            'customer_phone' => $this->faker->phoneNumber(),
            'status' => $status,
            'total_amount' => $this->faker->numberBetween(500, 5000),
            'paid_amount' => $status === 'confirmed' ? $this->faker->numberBetween(0, 5000) : 0,
            'currency' => $currency,
            'confirmed_at' => $status === 'confirmed' ? now() : null,
            'metadata' => null,
        ];
    }

    /**
     * Indicate that the booking is confirmed.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
            'confirmed_at' => now(),
            'paid_amount' => $attributes['total_amount'] ?? $this->faker->numberBetween(500, 5000),
        ]);
    }

    /**
     * Indicate that the booking is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'confirmed_at' => null,
            'paid_amount' => 0,
        ]);
    }

    /**
     * Indicate that the booking is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'confirmed_at' => null,
            'paid_amount' => 0,
        ]);
    }

    /**
     * Set a specific total amount.
     */
    public function amount(float $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'total_amount' => $amount,
        ]);
    }
}
