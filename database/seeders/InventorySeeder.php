<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HotelRoomType;
use App\Models\Inventory;
use Illuminate\Support\Carbon;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        $hotelRoomTypes = HotelRoomType::where('status', 'active')->get();
        $daysToSeed = 90;
        $startDate = Carbon::now();

        $progressBar = $this->command->getOutput()->createProgressBar($hotelRoomTypes->count() * $daysToSeed);

        foreach ($hotelRoomTypes as $roomType) {
            for ($i = 0; $i < $daysToSeed; $i++) {
                $nightDate = $startDate->copy()->addDays($i)->toDateString();

                // Calculate available units based on room type and random variation
                $baseUnits = match ($roomType->max_occupancy) {
                    1 => 10,  // Single rooms
                    2 => 15,  // Double/Twin rooms
                    3 => 8,   // Triple rooms
                    4 => 6,   // Family/Suite
                    default => 10,
                };

                $availableUnits = $baseUnits + $this->faker()->numberBetween(-3, 5);
                $availableUnits = max(1, $availableUnits); // Ensure at least 1

                $heldUnits = $this->faker()->numberBetween(0, min(3, $availableUnits));

                // 10% chance of stop sell
                $stopSell = $this->faker()->boolean(10);

                Inventory::updateOrCreate(
                    [
                        'hotel_room_type_id' => $roomType->id,
                        'night_date' => $nightDate,
                    ],
                    [
                        'available_units' => $availableUnits,
                        'held_units' => $heldUnits,
                        'stop_sell' => $stopSell,
                    ]
                );

                $progressBar->advance();
            }
        }

        $progressBar->finish();
        $this->command->line('');
        $this->command->info('Inventory seeded successfully.');
    }

    private function faker()
    {
        return \Illuminate\Support\Faker\Factory::create();
    }
}
