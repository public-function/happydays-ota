<?php
namespace App\Services;

use App\Models\Booking;
use App\Models\InventoryHold;

class BookingService
{
    protected InventoryHoldService $holdService;
    
    public function __construct(InventoryHoldService $holdService)
    {
        $this->holdService = $holdService;
    }
    
    public function createBookingFromHold(InventoryHold $hold, array $data): Booking
    {
        return $this->holdService->convertToBooking($hold, $data);
    }
    
    public function cancelBooking(Booking $booking): Booking
    {
        if ($booking->status === 'cancelled') {
            throw new \Exception('Booking is already cancelled');
        }
        
        $booking->status = 'cancelled';
        $booking->save();
        
        // Restore inventory if there was a hold
        if ($booking->inventoryHold) {
            $this->holdService->expireHold($booking->inventoryHold);
        }
        
        return $booking;
    }
}
