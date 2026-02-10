<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryHold;
use App\Models\InventoryHoldItem;
use App\Models\HotelRoomType;
use App\Models\ProductOffer;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InventoryHoldService
{
    protected const HOLD_DURATION_MINUTES = 15;

    /**
     * Create a new inventory hold.
     */
    public function createHold(
        ProductOffer $productOffer,
        HotelRoomType $hotelRoomType,
        \DateTimeInterface $checkInDate,
        int $quantity = 1,
        ?int $customerId = null
    ): InventoryHold
    {
        return DB::transaction(function () use ($productOffer, $hotelRoomType, $checkInDate, $quantity, $customerId) {
            // Validate availability first
            $nights = $productOffer->duration_nights;
            
            for ($i = 0; $i < $nights; $i++) {
                $nightDate = Carbon::parse($checkInDate)->addDays($i)->startOfDay();
                
                $inventory = Inventory::where('product_offer_id', $productOffer->id)
                    ->where('hotel_room_type_id', $hotelRoomType->id)
                    ->where('date', $nightDate)
                    ->lockForUpdate()
                    ->first();

                if (!$inventory) {
                    throw new \RuntimeException("No inventory found for date: {$nightDate->format('Y-m-d')}");
                }

                if ($inventory->stop_sell) {
                    throw new \RuntimeException("Stop sell is active for date: {$nightDate->format('Y-m-d')}");
                }

                $availableAfterHold = $inventory->available_units - $inventory->held_units - $quantity;
                if ($availableAfterHold < 0) {
                    throw new \RuntimeException("Insufficient inventory for date: {$nightDate->format('Y-m-d')}");
                }
            }

            // Create the hold
            $hold = InventoryHold::create([
                'token' => $this->generateHoldToken(),
                'product_offer_id' => $productOffer->id,
                'hotel_room_type_id' => $hotelRoomType->id,
                'check_in_date' => $checkInDate,
                'quantity' => $quantity,
                'customer_id' => $customerId,
                'status' => InventoryHold::STATUS_ACTIVE,
                'expires_at' => now()->addMinutes(self::HOLD_DURATION_MINUTES),
            ]);

            // Create hold items and increment held units
            for ($i = 0; $i < $nights; $i++) {
                $nightDate = Carbon::parse($checkInDate)->addDays($i)->startOfDay();
                
                $inventory = Inventory::where('product_offer_id', $productOffer->id)
                    ->where('hotel_room_type_id', $hotelRoomType->id)
                    ->where('date', $nightDate)
                    ->lockForUpdate()
                    ->first();

                InventoryHoldItem::create([
                    'inventory_hold_id' => $hold->id,
                    'inventory_id' => $inventory->id,
                    'night_date' => $nightDate,
                    'quantity' => $quantity,
                ]);

                $inventory->increment('held_units', $quantity);
            }

            return $hold;
        });
    }

    /**
     * Convert a hold to a booking.
     */
    public function convertHold(InventoryHold $hold): \App\Models\Booking
    {
        return DB::transaction(function () use ($hold) {
            if (!$hold->isActive()) {
                throw new \RuntimeException('Hold is not active or has expired');
            }

            // Create booking from hold
            $booking = \App\Models\Booking::create([
                'inventory_hold_id' => $hold->id,
                'reference' => $this->generateBookingReference(),
                'customer_name' => 'Pending Customer',
                'customer_email' => 'pending@example.com',
                'status' => \App\Models\Booking::STATUS_PENDING,
            ]);

            // Create booking items
            $nights = $hold->productOffer->duration_nights;
            $quantity = $hold->quantity;
            
            foreach ($hold->holdItems as $holdItem) {
                \App\Models\BookingItem::create([
                    'booking_id' => $booking->id,
                    'product_offer_id' => $holdItem->inventory->product_offer_id,
                    'hotel_room_type_id' => $holdItem->inventory->hotel_room_type_id,
                    'check_in_date' => $hold->check_in_date,
                    'nights' => $nights,
                    'quantity' => $quantity,
                    'unit_price' => $holdItem->inventory->price ?? 0,
                    'total_price' => ($holdItem->inventory->price ?? 0) * $nights * $quantity,
                    'status' => \App\Models\BookingItem::STATUS_PENDING,
                ]);

                // Decrement held units and available units
                $holdItem->inventory->decrement('held_units', $quantity);
                $holdItem->inventory->decrement('available_units', $quantity);
            }

            // Update hold status
            $hold->update([
                'status' => InventoryHold::STATUS_CONVERTED,
                'converted_at' => now(),
            ]);

            return $booking;
        });
    }

    /**
     * Cancel a hold and release inventory.
     */
    public function cancelHold(InventoryHold $hold): void
    {
        DB::transaction(function () use ($hold) {
            if ($hold->status !== InventoryHold::STATUS_ACTIVE) {
                return;
            }

            // Release held units
            foreach ($hold->holdItems as $holdItem) {
                $holdItem->inventory->decrement('held_units', $holdItem->quantity);
            }

            $hold->update(['status' => InventoryHold::STATUS_CANCELLED]);
        });
    }

    /**
     * Expire holds that have passed their expiry time.
     */
    public function expireHolds(): int
    {
        $expiredHolds = InventoryHold::where('status', InventoryHold::STATUS_ACTIVE)
            ->where('expires_at', '<', now())
            ->get();

        foreach ($expiredHolds as $hold) {
            $this->cancelHold($hold);
            $hold->update(['status' => InventoryHold::STATUS_EXPIRED]);
        }

        return $expiredHolds->count();
    }

    /**
     * Extend a hold's expiry time.
     */
    public function extendHold(InventoryHold $hold, int $minutes = 15): InventoryHold
    {
        if (!$hold->isActive()) {
            throw new \RuntimeException('Cannot extend a non-active hold');
        }

        $hold->update([
            'expires_at' => $hold->expires_at->addMinutes($minutes),
        ]);

        return $hold;
    }

    /**
     * Check if hold can be extended.
     */
    public function canExtend(InventoryHold $hold): bool
    {
        if (!$hold->isActive()) {
            return false;
        }

        // Don't allow extending if hold is about to expire within 5 minutes
        return $hold->expires_at->diffInMinutes(now()) > 5;
    }

    /**
     * Generate a unique hold token.
     */
    protected function generateHoldToken(): string
    {
        do {
            $token = Str::random(64);
        } while (InventoryHold::where('token', $token)->exists());

        return $token;
    }

    /**
     * Generate a booking reference.
     */
    protected function generateBookingReference(): string
    {
        $timestamp = now()->format('ymdHis');
        $random = strtoupper(substr(md5(uniqid()), 0, 4));
        return 'HBK' . $timestamp . $random;
    }
}
