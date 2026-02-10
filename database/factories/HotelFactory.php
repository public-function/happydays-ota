<?php

namespace Database\Factories;

use App\Models\Hotel;
use Illuminate\Database\Eloquent\Factories\Factory;

class HotelFactory extends Factory
{
    protected $model = Hotel::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company() . ' Hotel',
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'country' => $this->faker->countryCode(),
            'postal_code' => $this->faker->postcode(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->safeEmail(),
            'website' => $this->faker->url(),
            'status' => 'active',
            'checkin_time' => '14:00:00',
            'checkout_time' => '12:00:00',
            'timezone' => 'UTC',
        ];
    }

    public function inactive(): self
    {
        return $this->state(['status' => 'inactive']);
    }

    public function archived(): self
    {
        return $this->state(['status' => 'archived']);
    }
}
