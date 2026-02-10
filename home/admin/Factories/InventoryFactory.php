<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventory>
 */
class InventoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $availableUnits = $this->faker->numberBetween(5, 20);
        $heldUnits = $this->faker->numberBetween(0, min(3, $availableUnits));
        
        // 90% of the time, stop_sell is false
        $stopSell = $this->faker->boolean(10);

        return [
            'night_date' => Carbon::now()->addDays($this->faker->numberBetween(0, 89))->toDateString(),
            'available_units' => $availableUnits,
            'held_units' => $heldUnits,
            'stop_sell' => $stopSell,
        ];
    }

    /**
     * Set specific date for the inventory.
     */
    public function date(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'night_date' => $date,
        ]);
    }

    /**
     * Set stop sell to true.
     */
    public function stopSell(): static
    {
        return $this->state(fn (array $attributes) => [
            'stop_sell' => true,
        ]);
    }

    /**
     * Set stop sell to false.
     */
    public function available(): static
    {
        return $this->state(fn (array $attributes) => [
            'stop_sell' => false,
        ]);
    }
}
