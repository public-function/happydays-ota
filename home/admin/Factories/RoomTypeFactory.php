<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RoomType>
 */
class RoomTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $roomTypes = [
            [
                'code' => 'single',
                'name' => 'Single Room',
                'description' => 'A comfortable room with a single bed, perfect for solo travelers.',
                'default_max_occupancy' => 1,
            ],
            [
                'code' => 'double',
                'name' => 'Double Room',
                'description' => 'A cozy room with a double bed, ideal for couples.',
                'default_max_occupancy' => 2,
            ],
            [
                'code' => 'twin',
                'name' => 'Twin Room',
                'description' => 'A room with two single beds, perfect for friends or colleagues.',
                'default_max_occupancy' => 2,
            ],
            [
                'code' => 'family',
                'name' => 'Family Room',
                'description' => 'Spacious room with multiple beds, perfect for families.',
                'default_max_occupancy' => 4,
            ],
            [
                'code' => 'suite',
                'name' => 'Suite',
                'description' => 'Luxurious suite with separate living area and premium amenities.',
                'default_max_occupancy' => 4,
            ],
        ];

        $roomType = $this->faker->randomElement($roomTypes);

        return [
            'code' => $roomType['code'],
            'name' => $roomType['name'],
            'description' => $roomType['description'],
            'default_max_occupancy' => $roomType['default_max_occupancy'],
        ];
    }
}
