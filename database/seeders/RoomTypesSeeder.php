<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RoomType;

class RoomTypesSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        $roomTypes = [
            [
                'code' => 'single',
                'name' => 'Single Room',
                'description' => 'A comfortable room with a single bed, perfect for solo travelers. Features a desk, flat-screen TV, and private bathroom with shower.',
                'default_max_occupancy' => 1,
            ],
            [
                'code' => 'double',
                'name' => 'Double Room',
                'description' => 'A cozy room with a double bed, ideal for couples. Includes a seating area, minibar, and modern bathroom with bathtub.',
                'default_max_occupancy' => 2,
            ],
            [
                'code' => 'twin',
                'name' => 'Twin Room',
                'description' => 'A room with two single beds, perfect for friends or colleagues. Offers ample workspace and comfortable accommodation.',
                'default_max_occupancy' => 2,
            ],
            [
                'code' => 'family',
                'name' => 'Family Room',
                'description' => 'Spacious room with multiple beds, perfect for families. Features a sitting area, extra storage, and family-friendly amenities.',
                'default_max_occupancy' => 4,
            ],
            [
                'code' => 'suite',
                'name' => 'Suite',
                'description' => 'Luxurious suite with separate living area and premium amenities. Includes premium bedding, jacuzzi, and stunning views.',
                'default_max_occupancy' => 4,
            ],
        ];

        foreach ($roomTypes as $type) {
            RoomType::updateOrCreate(
                ['code' => $type['code']],
                $type
            );
        }

        $this->command->info('Room types seeded successfully.');
    }
}
