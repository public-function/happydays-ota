<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\ProductOffer;
use App\Models\HotelRoomType;
use Illuminate\Support\Carbon;
use Faker\Factory as Faker;

class SampleBookingsSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        $this->command->info('Creating sample bookings...');

        $faker = Faker::create('da_DK');
        $productOffers = ProductOffer::where('status', 'active')->get();
        $hotelRoomTypes = HotelRoomType::where('status', 'active')->get();

        if ($productOffers->isEmpty() || $hotelRoomTypes->isEmpty()) {
            $this->command->warn('No product offers or hotel room types found. Skipping sample bookings.');
            return;
        }

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
            $reference = 'BK-' . date('Y') . '-' . strtoupper($faker->lexify('??????'));

            // Customer details
            $customerName = $faker->name();
            $customerEmail = $faker->safeEmail();

            // Create the booking
            $booking = Booking::create([
                'booking_reference' => $reference,
                'customer_name' => $customerName,
                'customer_email' => $customerEmail,
                'customer_phone' => $faker->phoneNumber ?? $faker->e164PhoneNumber,
                'status' => $status,
                'total_amount' => 0,
                'paid_amount' => 0,
                'currency' => 'EUR',
                'confirmed_at' => $status === 'confirmed' ? now() : null,
            ]);

            // Create 1-3 booking items
            $itemCount = $faker->numberBetween(1, 3);
            $bookingTotal = 0;

            for ($j = 0; $j < $itemCount; $j++) {
                $productOffer = $productOffers->random();
                $roomType = $hotelRoomTypes->where('hotel_id', $productOffer->hotel_id)->first();

                if (!$roomType) {
                    continue;
                }

                $nights = $productOffer->duration_nights;
                $checkInDate = Carbon::now()->addDays($faker->numberBetween(7, 60))->format('Y-m-d');
                $quantity = $faker->numberBetween(1, 2);
                $adults = $faker->numberBetween(1, 4);
                $children = $faker->numberBetween(0, 2);
                $unitPrice = $productOffer->base_price / $nights;
                $totalPrice = $unitPrice * $nights * $quantity;

                // Item status follows booking status
                $itemStatusRoll = $faker->numberBetween(1, 100);
                $itemStatus = match (true) {
                    $itemStatusRoll <= 85 => 'confirmed',
                    $itemStatusRoll <= 95 => 'pending',
                    default => 'cancelled',
                };

                $bookingItem = BookingItem::create([
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
                    'hotel_name_snapshot' => 'Hotel', // Placeholder
                    'offer_name_snapshot' => $productOffer->name,
                    'rate_plan_name_snapshot' => 'Standard',
                ]);

                $bookingTotal += $totalPrice;
            }

            // Update booking total
            $booking->update(['total_amount' => $bookingTotal]);

            // For confirmed bookings, set paid amount to 80-100% of total
            if ($status === 'confirmed') {
                $booking->update([
                    'paid_amount' => $bookingTotal * $faker->numberBetween(80, 100) / 100,
                ]);
            }
        }

        $this->command->info("Created {$bookingCount} sample bookings.");
    }
}
