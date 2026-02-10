<?php

namespace Database\Factories;

use App\Models\RatePlan;
use Illuminate\Database\Eloquent\Factories\Factory;

class RatePlanFactory extends Factory
{
    protected $model = RatePlan::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->lexify('???')),
            'name' => $this->faker->word() . ' Rate',
            'description' => $this->faker->sentence(),
            'cancellation_policy' => [
                'type' => 'free_cancellation',
                'days_before_checkin' => 3,
            ],
            'status' => 'active',
        ];
    }
}
