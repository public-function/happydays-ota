<?php
namespace App\Services;

use App\Models\InventoryHold;
use App\Models\InventoryHoldItem;
use App\Models\HotelRoomTypeInventory;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class InventoryHoldService
{
    protected int $ttlMinutes = 15;
    
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
            
            // Lock and update inventory
            $holdItems = [];
            foreach ($availability['nights'] as $nightDate) {
                $inventory = HotelRoomTypeInventory::where('hotel_room_type_id', $data['hotel_room_type_id'])
                    ->where('night_date', $nightDate)
                    ->lockForUpdate()
                    ->first();
                
                $inventory->held_units += ($data['quantity'] ?? 1);
                $inventory->save();
                
                $holdItems[] = [
                    'hotel_room_type_id' => $data['hotel_room_type_id'],
                    'night_date' => $nightDate,
                    'quantity' => $data['quantity'] ?? 1,
                ];
            }
            
            // Create hold
            $hold = InventoryHold::create([
                'hold_token' => Uuid::uuid4()->toString(),
                'status' => 'active',
                'expires_at' => now()->addMinutes($this->ttlMinutes),
                'customer_id' => $data['customer_id'] ?? null,
            ]);
            
            // Create hold items
            foreach ($holdItems as $item) {
                $hold->items()->create($item);
            }
            
            return $hold;
        });
    }
    
    public function convertToBooking(InventoryHold $hold, array $bookingData): \App\Models\Booking
    {
        return DB::transaction(function () use ($hold, $bookingData) {
            if ($hold->status !== 'active' || $hold->expires_at->isPast()) {
                throw new \Exception('Hold has expired or is not active');
            }
            
            // Lock inventory again
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
            $booking = \App\Models\Booking::create([
                'booking_reference' => $this->generateBookingReference(),
                'customer_name' => $bookingData['customer_name'],
                'customer_email' => $bookingData['customer_email'],
                'customer_phone' => $bookingData['customer_phone'] ?? null,
                'status' => 'confirmed',
                'currency' => 'EUR',
                'total_amount' => $bookingData['total_amount'],
                'inventory_hold_id' => $hold->id,
            ]);
            
            // Mark hold converted
            $hold->status = 'converted';
            $hold->booking_id = $booking->id;
            $hold->save();
            
            return $booking;
        });
    }
    
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
    
    protected function generateBookingReference(): string
    {
        $count = \App\Models\Booking::count() + 1;
        return 'BK-' . date('Y') . '-' . str_pad($count, 6, '0', STR_PAD_LEFT);
    }
}
