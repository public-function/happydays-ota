<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Inventory;
use App\Models\InventoryHold;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class BookingService
{
    /**
     * Generate a unique booking reference.
     */
    public function generateBookingReference(): string
    {
        $timestamp = now()->format('ymd');
        $random = strtoupper(Str::random(6));
        return 'HBK' . $timestamp . '-' . $random;
    }

    /**
     * Create a new booking from a hold.
     */
    public function createBookingFromHold(InventoryHold $hold): Booking
    {
        return DB::transaction(function () use ($hold) {
            // Validate hold
            if (!$hold->isActive()) {
                throw new \RuntimeException('Hold is not active or has expired');
            }

            // Create booking
            $booking = Booking::create([
                'reference' => $this->generateBookingReference(),
                'inventory_hold_id' => $hold->id,
                'customer_name' => 'Guest',
                'customer_email' => 'guest@example.com',
                'status' => Booking::STATUS_PENDING,
            ]);

            // Create booking items from hold items
            $nights = $hold->productOffer->duration_nights;
            $quantity = $hold->quantity;

            foreach ($hold->holdItems as $holdItem) {
                $bookingItem = BookingItem::create([
                    'booking_id' => $booking->id,
                    'product_offer_id' => $holdItem->inventory->product_offer_id,
                    'hotel_room_type_id' => $holdItem->inventory->hotel_room_type_id,
                    'check_in_date' => $hold->check_in_date,
                    'nights' => $nights,
                    'quantity' => $quantity,
                    'unit_price' => $holdItem->inventory->price ?? 0,
                    'total_price' => ($holdItem->inventory->price ?? 0) * $nights * $quantity,
                    'status' => BookingItem::STATUS_PENDING,
                ]);

                // Update inventory - decrement held and available units
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
     * Cancel a booking and restore inventory.
     */
    public function cancelBooking(Booking $booking): void
    {
        DB::transaction(function () use ($booking) {
            if ($booking->status === Booking::STATUS_CANCELLED) {
                return;
            }

            // Update booking status
            $booking->update(['status' => Booking::STATUS_CANCELLED]);

            // Restore inventory for each booking item
            foreach ($booking->items as $item) {
                $this->restoreInventoryForItem($item);
            }
        });
    }

    /**
     * Restore inventory for a booking item.
     */
    protected function restoreInventoryForItem(BookingItem $item): void
    {
        $nights = $item->nights;
        $checkIn = $item->check_in_date;
        $quantity = $item->quantity;

        for ($i = 0; $i < $nights; $i++) {
            $nightDate = $checkIn->copy()->addDays($i);
            
            $inventory = Inventory::where('product_offer_id', $item->product_offer_id)
                ->where('hotel_room_type_id', $item->hotel_room_type_id)
                ->where('date', $nightDate)
                ->first();

            if ($inventory) {
                $inventory->increment('available_units', $quantity);
            }
        }
    }

    /**
     * Confirm a booking.
     */
    public function confirmBooking(Booking $booking): Booking
    {
        if ($booking->status !== Booking::STATUS_PENDING) {
            throw new \RuntimeException('Only pending bookings can be confirmed');
        }

        $booking->update([
            'status' => Booking::STATUS_CONFIRMED,
            'confirmed_at' => now(),
        ]);

        // Update booking items status
        $booking->items()->update(['status' => BookingItem::STATUS_CONFIRMED]);

        return $booking;
    }

    /**
     * Calculate total amount for a booking.
     */
    public function calculateTotalAmount(Booking $booking): float
    {
        return $booking->items->sum('total_price');
    }

    /**
     * Update booking totals.
     */
    public function updateBookingTotals(Booking $booking): Booking
    {
        $totalAmount = $this->calculateTotalAmount($booking);
        $booking->update(['total_amount' => $totalAmount]);

        return $booking;
    }

    /**
     * Get booking summary.
     */
    public function getBookingSummary(Booking $booking): array
    {
        return [
            'reference' => $booking->reference,
            'status' => $booking->status,
            'customer_name' => $booking->customer_name,
            'customer_email' => $booking->customer_email,
            'total_amount' => $booking->total_amount,
            'currency' => $booking->currency,
            'items_count' => $booking->items->count(),
            'nights' => $booking->items->first()?->nights ?? 0,
            'quantity' => $booking->items->first()?->quantity ?? 0,
            'created_at' => $booking->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
