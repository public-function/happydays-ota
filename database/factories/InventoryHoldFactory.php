<?php

namespace Database\Factories;

use App\Models\ProductOffer;
use App\Models\HotelRoomType;
use App\Models\InventoryHold;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class InventoryHoldFactory extends Factory
{
    protected $model = InventoryHold::class;

    public function definition(): array
    {
        return [
            'token' => \Illuminate\Support\Str::random(64),
            'product_offer_id' => ProductOffer::factory(),
            'hotel_room_type_id' => HotelRoomType::factory(),
            'check_in_date' => $this->faker->dateTimeBetween('now', '+30 days'),
            'quantity' => $this->faker->numberBetween(1, 3),
            'customer_id' => null,
            'status' => 'active',
            'expires_at' => now()->addMinutes(15),
            'converted_at' => null,
        ];
    }

    public function expired(): self
    {
        return $this->state([
            'status' => 'expired',
            'expires_at' => now()->subMinutes(1),
        ]);
    }

    public function converted(): self
    {
        return $this->state([
            'status' => 'converted',
            'converted_at' => now(),
        ]);
    }

    public function cancelled(): self
    {
        return $this->state(['status' => 'cancelled']);
    }
}
