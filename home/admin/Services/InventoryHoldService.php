<?php

namespace App\Services;

use App\Models\InventoryHold;
use App\Models\InventoryHoldItem;
use App\Models\HotelRoomTypeInventory;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\ProductOffer;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class InventoryHoldService
{
    protected int $ttlMinutes;

    public function __construct()
    {
        $this->ttlMinutes = config('ota.hold_ttl_minutes', 15);
    }

    /**
     * Create a new inventory hold
     */
    public function createHold(array $data): InventoryHold
    {
        return DB::transaction(function () use ($data) {
            // Validate availability first
            $availability = app(AvailabilityService::class)->checkAvailability(
                $data['product_offer_id'],
                $data['hotel_room_type_id'],
                $data['check_in_date'],
                $data['quantity'] ?? 1
            );

            if (!$availability['available']) {
                throw new \Exception('Insufficient inventory: ' . implode(', ', $availability['errors']));
            }

            $quantity = $data['quantity'] ?? 1;

            // Lock and update inventory for each required night
            foreach ($availability['nights'] as $nightDate) {
                $inventory = HotelRoomTypeInventory::where('hotel_room_type_id', $data['hotel_room_type_id'])
                    ->where('night_date', $nightDate)
                    ->lockForUpdate()
                    ->first();

                $inventory->held_units += $quantity;
                $inventory->save();
            }

            // Create hold
            $hold = InventoryHold::create([
                'hold_token' => Uuid::uuid4()->toString(),
                'status' => 'active',
                'expires_at' => now()->addMinutes($this->ttlMinutes),
                'customer_id' => $data['customer_id'] ?? null,
            ]);

            // Create hold items
            foreach ($availability['nights'] as $nightDate) {
                $hold->items()->create([
                    'hotel_room_type_id' => $data['hotel_room_type_id'],
                    'night_date' => $nightDate,
                    'quantity' => $quantity,
                ]);
            }

            return $hold;
        });
    }

    /**
     * Convert a hold to a booking
     */
    public function convertToBooking(InventoryHold $hold, array $bookingData, array $bookingItemsData): Booking
    {
        return DB::transaction(function () use ($hold, $bookingData, $bookingItemsData) {
            if ($hold->status !== 'active') {
                throw new \Exception('Hold is not active');
            }

            if ($hold->expires_at->isPast()) {
                throw new \Exception('Hold has expired');
            }

            // Lock inventory and update
            foreach ($hold->items as $item) {
                $inventory = HotelRoomTypeInventory::where('hotel_room_type_id', $item->hotel_room_type_id)
                    ->where('night_date', $item->night_date)
                    ->lockForUpdate()
                    ->first();

                $inventory->available_units -= $item->quantity;
                $inventory->held_units -= $item->quantity;
                $inventory->save();
            }

            // Create booking
            $booking = Booking::create([
                'booking_reference' => $this->generateBookingReference(),
                'customer_name' => $bookingData['customer_name'],
                'customer_email' => $bookingData['customer_email'],
                'customer_phone' => $bookingData['customer_phone'] ?? null,
                'status' => 'confirmed',
                'currency' => 'EUR',
                'total_amount' => $bookingData['total_amount'],
                'inventory_hold_id' => $hold->id,
            ]);

            // Create booking items with snapshots
            foreach ($bookingItemsData as $itemData) {
                $offer = ProductOffer::with(['hotel', 'ratePlan', 'hotelRoomTypes'])->find($itemData['product_offer_id']);
                $hotelRoomType = $offer->hotelRoomTypes->first();

                $bookingItem = $booking->items()->create([
                    'product_offer_id' => $itemData['product_offer_id'],
                    'hotel_room_type_id' => $itemData['hotel_room_type_id'],
                    'check_in_date' => $itemData['check_in_date'],
                    'nights' => $itemData['nights'],
                    'adults' => $itemData['adults'],
                    'children' => $itemData['children'] ?? 0,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'status' => 'confirmed',
                    // Snapshot fields
                    'hotel_name_snapshot' => $offer->hotel->name,
                    'offer_name_snapshot' => $offer->name,
                    'rate_plan_name_snapshot' => $offer->ratePlan->name,
                    'hotel_room_type_code_snapshot' => $hotelRoomType->supplier_code,
                    'hotel_room_type_name_snapshot' => $hotelRoomType->supplier_name,
                ]);
            }

            // Mark hold converted
            $hold->status = 'converted';
            $hold->booking_id = $booking->id;
            $hold->save();

            return $booking;
        });
    }

    /**
     * Expire a hold (release inventory)
     */
    public function expireHold(InventoryHold $hold): void
    {
        DB::transaction(function () use ($hold) {
            foreach ($hold->items as $item) {
                $inventory = HotelRoomTypeInventory::where('hotel_room_type_id', $item->hotel_room_type_id)
                    ->where('night_date', $item->night_date)
                    ->lockForUpdate()
                    ->first();

                $inventory->held_units -= $item->quantity;
                $inventory->save();
            }

            $hold->status = 'expired';
            $hold->save();
        });
    }

    /**
     * Cancel a hold (same as expire but different status)
     */
    public function cancelHold(InventoryHold $hold): void
    {
        DB::transaction(function () use ($hold) {
            foreach ($hold->items as $item) {
                $inventory = HotelRoomTypeInventory::where('hotel_room_type_id', $item->hotel_room_type_id)
                    ->where('night_date', $item->night_date)
                    ->lockForUpdate()
                    ->first();

                $inventory->held_units -= $item->quantity;
                $inventory->save();
            }

            $hold->status = 'cancelled';
            $hold->save();
        });
    }

    /**
     * Generate a human-friendly booking reference
     */
    protected function generateBookingReference(): string
    {
        $count = Booking::whereYear('created_at', date('Y'))->count() + 1;
        return 'BK-' . date('Y') . '-' . str_pad($count, 6, '0', STR_PAD_LEFT);
    }
}
