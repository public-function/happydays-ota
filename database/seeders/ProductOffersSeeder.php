<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Hotel;
use App\Models\RatePlan;
use App\Models\ProductOffer;
use App\Models\HotelRoomType;
use Faker\Factory as Faker;

class ProductOffersSeeder extends Seeder
{
    private $faker;

    public function __construct()
    {
        $this->faker = Faker::create('da_DK');
    }
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        $hotels = Hotel::all();
        $ratePlans = RatePlan::where('status', 'active')->get();

        $offerTemplates = [
            [
                'duration' => 3,
                'offers' => [
                    ['name' => '3 nights with breakfast & dinner', 'board_type' => 'HB'],
                    ['name' => '3 nights city break', 'board_type' => 'BB'],
                    ['name' => '3 nights romantic escape', 'board_type' => 'BB'],
                    ['name' => '3 nights business stay', 'board_type' => 'RO'],
                ],
            ],
            [
                'duration' => 5,
                'offers' => [
                    ['name' => '5 nights breakfast only', 'board_type' => 'BB'],
                    ['name' => '5 nights all-inclusive getaway', 'board_type' => 'AI'],
                    ['name' => '5 nights weekend extended', 'board_type' => 'HB'],
                    ['name' => '5 nights culture explorer', 'board_type' => 'BB'],
                ],
            ],
            [
                'duration' => 7,
                'offers' => [
                    ['name' => '7 nights summer holiday', 'board_type' => 'AI'],
                    ['name' => '7 nights winter retreat', 'board_type' => 'FB'],
                    ['name' => '7 nights wellness package', 'board_type' => 'HB'],
                    ['name' => '7 nights family vacation', 'board_type' => 'FB'],
                ],
            ],
            [
                'duration' => 14,
                'offers' => [
                    ['name' => '14 nights long stay', 'board_type' => 'RO'],
                    ['name' => '2 weeks all-inclusive', 'board_type' => 'AI'],
                    ['name' => '14 nights explorer package', 'board_type' => 'BB'],
                ],
            ],
        ];

        foreach ($hotels as $hotel) {
            foreach ($offerTemplates as $template) {
                foreach ($template['offers'] as $offer) {
                    $ratePlan = $ratePlans->where('board_type', $offer['board_type'])->first();

                    if ($ratePlan) {
                        // Base prices in DKK - vary by city
                        $cityMultipliers = [
                            'Copenhagen' => 1.5,
                            'Aarhus' => 1.2,
                            'Skagen' => 1.3,
                            'Aalborg' => 1.0,
                            'Odense' => 1.0,
                        ];

                        $multiplier = $cityMultipliers[$hotel->city] ?? 1.0;

                        $basePrice = match ($template['duration']) {
                            3 => 3000 * $multiplier,
                            5 => 5000 * $multiplier,
                            7 => 7000 * $multiplier,
                            14 => 12000 * $multiplier,
                            default => 5000 * $multiplier,
                        };

                        ProductOffer::updateOrCreate(
                            [
                                'hotel_id' => $hotel->id,
                                'rate_plan_id' => $ratePlan->id,
                                'name' => $offer['name'],
                            ],
                            [
                                'duration_nights' => $template['duration'],
                                'min_guests' => 1,
                                'max_guests' => $this->faker->numberBetween(2, 4),
                                'base_price' => $basePrice,
                                'status' => 'active',
                            ]
                        );
                    }
                }
            }
        }

        $this->command->info('Product offers seeded successfully.');
    }
}
