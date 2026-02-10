<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\ProductOffer;
use App\Models\HotelRoomType;
use Illuminate\Support\Carbon;

class SampleBookingsSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        $this->command->info('Creating sample bookings...');

        $faker = \Illuminate\Support\Faker\Factory::create();
        $productOffers = ProductOffer::where('status', 'active')->get();
        $hotelRoomTypes = HotelRoomType::where('status', 'active')->get();

        // Create 10-20 sample bookings
        $bookingCount = $faker->numberBetween(10, 20);

        for ($i = 0; $i < $bookingCount; $i++) {
            // Determine booking status (80% confirmed, 15% pending, 5% cancelled)
            $statusRoll = $faker->numberBetween(1, 100);
            $status = match (true) {
                $statusRoll <= 80 => 'confirmed',
                $statusRoll <= 95 => 'pending',
                default => 'cancelled',
            };

            // Generate booking reference
            $reference = 'HD' . strtoupper(\Illuminate\Support\Str::random(6));

            // Customer details
            $customerName = $faker->name();
            $customerEmail = $faker->safeEmail();

            // Total amount (will be calculated from items)
            $totalAmount = 0;

            // Create the booking
            $booking = Booking::create([
                'reference' => $reference,
                'customer_name' => $customerName,
                'customer_email' => $customerEmail,
                'customer_phone' => $faker->phoneNumber(),
                'status' => $status,
                'total_amount' => 0, // Will be updated after items
                'paid_amount' => $status === 'confirmed' ? 0 : 0,
                'currency' => 'EUR',
                'confirmed_at' => $status === 'confirmed' ? now() : null,
            ]);

            // Create 1-3 booking items
            $itemCount = $faker->numberBetween(1, 3);
            $bookingTotal = 0;

            for ($j = 0; $j < $itemCount; $j++) {
                $productOffer = $productOffers->random();
                $roomType = $hotelRoomTypes->where('hotel_id', $productOffer->hotel_id)->random();

                $nights = $productOffer->duration_nights;
                $checkInDate = Carbon::now()->addDays($faker->numberBetween(7, 60))->toDateString();
                $quantity = $faker->numberBetween(1, 2);
                $adults = $faker->numberBetween(1, 4);
                $children = $faker->numberBetween(0, 2);
                $unitPrice = $productOffer->base_price / $productOffer->duration_nights;
                $totalPrice = $unitPrice * $nights * $quantity;

                // Item status follows booking status with some variation
                $itemStatusRoll = $faker->numberBetween(1, 100);
                $itemStatus = match (true) {
                    $itemStatusRoll <= 85 => 'confirmed',
                    $itemStatusRoll <= 95 => 'pending',
                    default => 'cancelled',
                };

                BookingItem::create([
                    'booking_id' => $booking->id,
                    'product_offer_id' => $productOffer->id,
                    'hotel_room_type_id' => $roomType->id,
                    'check_in_date' => $checkInDate,
                    'nights' => $nights,
                    'quantity' => $quantity,
                    'adults' => $adults,
                    'children' => $children,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'status' => $itemStatus,
                    'snapshot' => json_encode([
                        'hotel_name' => $productOffer->hotel->name,
                        'room_type' => $roomType->supplier_name,
                        'rate_plan' => $productOffer->ratePlan->name,
                        'offer_name' => $productOffer->name,
                    ]),
                ]);

                $bookingTotal += $totalPrice;
            }

            // Update booking total
            $booking->update(['total_amount' => $bookingTotal]);

            // For confirmed bookings, set paid amount to 80-100% of total
            if ($status === 'confirmed') {
                $booking->update([
                    'paid_amount' => $bookingTotal * ($faker->numberBetween(80, 100) / 100),
                ]);
            }
        }

        $this->command->info("Created {$bookingCount} sample bookings.");
    }
}
