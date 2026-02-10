<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\HotelRoomTypeInventory;
use Illuminate\Support\Facades\DB;

class BookingService
{
    protected InventoryHoldService $holdService;

    public function __construct(InventoryHoldService $holdService)
    {
        $this->holdService = $holdService;
    }

    /**
     * Create a booking from a hold
     */
    public function createBookingFromHold(string $holdToken, array $customerData, array $itemData, float $totalAmount): Booking
    {
        $hold = InventoryHold::where('hold_token', $holdToken)->firstOrFail();

        $bookingItemsData = [
            [
                'product_offer_id' => $itemData['product_offer_id'],
                'hotel_room_type_id' => $itemData['hotel_room_type_id'],
                'check_in_date' => $itemData['check_in_date'],
                'nights' => $itemData['nights'],
                'adults' => $itemData['adults'],
                'children' => $itemData['children'] ?? 0,
                'quantity' => $itemData['quantity'] ?? 1,
                'unit_price' => $itemData['unit_price'],
            ],
        ];

        return $this->holdService->convertToBooking($hold, $customerData + ['total_amount' => $totalAmount], $bookingItemsData);
    }

    /**
     * Cancel a booking item and restore inventory
     */
    public function cancelBookingItem(int $bookingItemId, string $notes = ''): BookingItem
    {
        return DB::transaction(function () use ($bookingItemId, $notes) {
            $item = BookingItem::findOrFail($bookingItemId);

            if ($item->status === 'cancelled') {
                throw new \Exception('Item is already cancelled');
            }

            // Restore inventory for each night
            $nights = $item->nights;
            $checkIn = \Carbon\Carbon::parse($item->check_in_date);

            for ($i = 0; $i < $nights; $i++) {
                $nightDate = $checkIn->addDays($i)->format('Y-m-d');

                $inventory = HotelRoomTypeInventory::where('hotel_room_type_id', $item->hotel_room_type_id)
                    ->where('night_date', $nightDate)
                    ->lockForUpdate()
                    ->first();

                if ($inventory) {
                    $inventory->available_units += $item->quantity;
                    $inventory->save();
                }
            }

            // Update item status
            $item->status = 'cancelled';
            $item->notes = $notes;
            $item->save();

            return $item;
        });
    }

    /**
     * Get booking with all items
     */
    public function getBooking(int $bookingId): Booking
    {
        return Booking::with(['items.productOffer', 'items.hotelRoomType', 'hold'])->findOrFail($bookingId);
    }

    /**
     * Search bookings
     */
    public function searchBookings(array $filters = [])
    {
        $query = Booking::with(['items']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['customer_email'])) {
            $query->where('customer_email', 'like', '%' . $filters['customer_email'] . '%');
        }

        if (isset($filters['booking_reference'])) {
            $query->where('booking_reference', 'like', '%' . $filters['booking_reference'] . '%');
        }

        return $query->orderBy('created_at', 'desc')->paginate(20);
    }
}
