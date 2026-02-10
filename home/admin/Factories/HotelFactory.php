<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Hotel>
 */
class HotelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cities = [
            'Skagen' => ['postal' => '9990', 'address' => 'Vestre Strandvej 35'],
            'Copenhagen' => ['postal' => '1250', 'address' => 'Kongens Nytorv 15'],
            'Aarhus' => ['postal' => '8000', 'address' => 'Banegårdspladsen 12'],
            'Aalborg' => ['postal' => '9000', 'address' => 'Jomfru Ane Gade 21'],
            'Odense' => ['postal' => '5000', 'address' => 'Vestergade 48'],
        ];

        $city = $this->faker->randomKey($cities);
        $cityData = $cities[$city];
        $hotelNames = [
            'Skagen' => ['Hotel Skagen', 'Grand Hotel Skagen', 'The Beach House Skagen'],
            'Copenhagen' => ['Hotel Copenhagen', 'The Imperial', 'Nobis Hotel Copenhagen'],
            'Aarhus' => ['Hotel Aarhus', 'Comwell Aarhus', 'Scandic Aarhus City'],
            'Aalborg' => ['Hotel Aalborg', 'Kul Hotel Aalborg', 'Comwell Hvide Hus'],
            'Odense' => ['Hotel Odense', 'First Hotel Odense', 'Den Hvide Cafe'],
        ];

        $name = $this->faker->randomElement($hotelNames[$city]);

        return [
            'name' => $name,
            'address' => $cityData['address'],
            'city' => $city,
            'country' => 'Denmark',
            'postal_code' => $cityData['postal'],
            'phone' => '+45 ' . $this->faker->numerify('########'),
            'email' => strtolower(str_replace(' ', '', $name)) . '@' . strtolower(str_replace(' ', '', $city)) . '.dk',
            'website' => 'https://' . strtolower(str_replace(' ', '', $name)) . '.dk',
            'status' => 'active',
            'checkin_time' => '15:00:00',
            'checkout_time' => '11:00:00',
            'timezone' => 'Europe/Copenhagen',
        ];
    }

    /**
     * Indicate that the hotel is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the hotel is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}
