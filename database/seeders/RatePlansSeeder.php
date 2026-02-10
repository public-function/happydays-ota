<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RatePlan;

class RatePlansSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        $ratePlans = [
            [
                'name' => 'Room Only',
                'board_type' => 'RO',
                'cancellation_policy' => json_encode([
                    'type' => 'free_cancellation',
                    'deadline_hours' => 24,
                    'description' => 'Free cancellation up to 24 hours before check-in',
                ]),
                'status' => 'active',
            ],
            [
                'name' => 'Bed & Breakfast',
                'board_type' => 'BB',
                'cancellation_policy' => json_encode([
                    'type' => 'free_cancellation',
                    'deadline_hours' => 48,
                    'description' => 'Free cancellation up to 48 hours before check-in',
                ]),
                'status' => 'active',
            ],
            [
                'name' => 'Half Board',
                'board_type' => 'HB',
                'cancellation_policy' => json_encode([
                    'type' => 'free_cancellation',
                    'deadline_hours' => 72,
                    'description' => 'Free cancellation up to 72 hours before check-in',
                ]),
                'status' => 'active',
            ],
            [
                'name' => 'Full Board',
                'board_type' => 'FB',
                'cancellation_policy' => json_encode([
                    'type' => 'free_cancellation',
                    'deadline_hours' => 72,
                    'description' => 'Free cancellation up to 72 hours before check-in',
                ]),
                'status' => 'active',
            ],
            [
                'name' => 'All Inclusive',
                'board_type' => 'AI',
                'cancellation_policy' => json_encode([
                    'type' => 'non_refundable',
                    'description' => 'Non-refundable rate - full payment required at booking',
                ]),
                'status' => 'active',
            ],
            [
                'name' => 'Flexible Rate',
                'board_type' => 'RO',
                'cancellation_policy' => json_encode([
                    'type' => 'free_cancellation',
                    'deadline_hours' => 0,
                    'description' => 'Free cancellation until check-in',
                ]),
                'status' => 'active',
            ],
        ];

        foreach ($ratePlans as $plan) {
            RatePlan::updateOrCreate(
                ['name' => $plan['name']],
                $plan
            );
        }

        $this->command->info('Rate plans seeded successfully.');
    }
}
