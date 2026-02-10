<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\ProductOffer;
use App\Models\HotelRoomType;
use App\Models\BookingItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingItemFactory extends Factory
{
    protected $model = BookingItem::class;

    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'product_offer_id' => ProductOffer::factory(),
            'hotel_room_type_id' => HotelRoomType::factory(),
            'check_in_date' => $this->faker->dateTimeBetween('now', '+30 days'),
            'nights' => $this->faker->numberBetween(1, 14),
            'quantity' => $this->faker->numberBetween(1, 3),
            'unit_price' => $this->faker->numberBetween(50, 500),
            'total_price' => $this->faker->numberBetween(100, 2000),
            'status' => 'pending',
            'snapshot' => null,
        ];
    }

    public function confirmed(): self
    {
        return $this->state(['status' => 'confirmed']);
    }

    public function cancelled(): self
    {
        return $this->state(['status' => 'cancelled']);
    }
}
