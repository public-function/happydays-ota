<?php

namespace Database\Factories;

use App\Models\Hotel;
use App\Models\RoomType;
use App\Models\HotelRoomType;
use Illuminate\Database\Eloquent\Factories\Factory;

class HotelRoomTypeFactory extends Factory
{
    protected $model = HotelRoomType::class;

    public function definition(): array
    {
        return [
            'hotel_id' => Hotel::factory(),
            'supplier_code' => strtoupper($this->faker->lexify('?????')),
            'supplier_name' => $this->faker->word() . ' Supplier',
            'max_occupancy' => $this->faker->numberBetween(1, 6),
            'min_occupancy' => 1,
            'status' => 'active',
        ];
    }

    public function inactive(): self
    {
        return $this->state(['status' => 'inactive']);
    }
}
