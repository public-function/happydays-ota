<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HotelRoomType>
 */
class HotelRoomTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $supplierCodes = [
            'single' => [
                'code' => 'ENK',
                'name' => 'Single Economy',
                'min_occupancy' => 1,
                'max_occupancy' => 1,
            ],
            'double' => [
                'code' => 'DBL2',
                'name' => 'Double Standard',
                'min_occupancy' => 1,
                'max_occupancy' => 2,
            ],
            'twin' => [
                'code' => 'TWN',
                'name' => 'Twin Room',
                'min_occupancy' => 1,
                'max_occupancy' => 2,
            ],
            'family' => [
                'code' => 'FAM',
                'name' => 'Family Room',
                'min_occupancy' => 2,
                'max_occupancy' => 4,
            ],
            'suite' => [
                'code' => 'STE',
                'name' => 'Suite',
                'min_occupancy' => 1,
                'max_occupancy' => 4,
            ],
            'double_premium' => [
                'code' => 'DBL4',
                'name' => 'Double Premium',
                'min_occupancy' => 1,
                'max_occupancy' => 2,
            ],
        ];

        $type = $this->faker->randomKey($supplierCodes);
        $data = $supplierCodes[$type];

        return [
            'supplier_code' => $data['code'],
            'supplier_name' => $data['name'],
            'min_occupancy' => $data['min_occupancy'],
            'max_occupancy' => $data['max_occupancy'],
            'status' => 'active',
        ];
    }

    /**
     * Indicate that the room type is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the room type is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}
