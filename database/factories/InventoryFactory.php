<?php

namespace Database\Factories;

use App\Models\ProductOffer;
use App\Models\HotelRoomType;
use App\Models\Inventory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class InventoryFactory extends Factory
{
    protected $model = Inventory::class;

    public function definition(): array
    {
        return [
            'product_offer_id' => ProductOffer::factory(),
            'hotel_room_type_id' => HotelRoomType::factory(),
            'date' => $this->faker->dateTimeBetween('now', '+90 days'),
            'total_units' => $this->faker->numberBetween(5, 20),
            'available_units' => $this->faker->numberBetween(0, 20),
            'held_units' => 0,
            'stop_sell' => false,
            'price' => $this->faker->numberBetween(50, 500),
        ];
    }

    public function withAvailableUnits(int $units): self
    {
        return $this->state([
            'available_units' => $units,
            'total_units' => max($units, $this->faker->numberBetween($units, $units + 10)),
        ]);
    }

    public function stopped(): self
    {
        return $this->state(['stop_sell' => true]);
    }

    public function forDate(Carbon $date): self
    {
        return $this->state(['date' => $date]);
    }
}
