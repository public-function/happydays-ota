<?php

namespace Database\Factories;

use App\Models\Hotel;
use App\Models\RatePlan;
use App\Models\ProductOffer;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductOfferFactory extends Factory
{
    protected $model = ProductOffer::class;

    public function definition(): array
    {
        return [
            'hotel_id' => Hotel::factory(),
            'rate_plan_id' => RatePlan::factory(),
            'name' => $this->faker->word() . ' Offer',
            'duration_nights' => $this->faker->numberBetween(1, 14),
            'min_guests' => 1,
            'max_guests' => $this->faker->numberBetween(2, 6),
            'base_price' => $this->faker->numberBetween(50, 500),
            'status' => 'active',
        ];
    }

    public function draft(): self
    {
        return $this->state(['status' => 'draft']);
    }

    public function paused(): self
    {
        return $this->state(['status' => 'paused']);
    }
}
