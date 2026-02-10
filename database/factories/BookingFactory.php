<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\InventoryHold;
use App\Models\BookingItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        return [
            'reference' => $this->generateReference(),
            'inventory_hold_id' => null,
            'customer_id' => null,
            'customer_name' => $this->faker->name(),
            'customer_email' => $this->faker->safeEmail(),
            'customer_phone' => $this->faker->phoneNumber(),
            'status' => 'pending',
            'total_amount' => $this->faker->numberBetween(100, 2000),
            'paid_amount' => 0,
            'currency' => 'USD',
            'metadata' => null,
            'confirmed_at' => null,
        ];
    }

    public function confirmed(): self
    {
        return $this->state([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);
    }

    public function cancelled(): self
    {
        return $this->state(['status' => 'cancelled']);
    }

    public function completed(): self
    {
        return $this->state(['status' => 'completed']);
    }

    public function withHold(InventoryHold $hold): self
    {
        return $this->state([
            'inventory_hold_id' => $hold->id,
        ]);
    }

    protected function generateReference(): string
    {
        $timestamp = now()->format('ymdHis');
        $random = strtoupper(substr(md5(uniqid()), 0, 4));
        return 'HBK' . $timestamp . $random;
    }
}
