<?php

namespace Database\Factories;

use App\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomTypeFactory extends Factory
{
    protected $model = RoomType::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->lexify('???')),
            'name' => $this->faker->word() . ' Room',
            'description' => $this->faker->sentence(),
            'default_max_occupancy' => $this->faker->numberBetween(1, 6),
        ];
    }
}
