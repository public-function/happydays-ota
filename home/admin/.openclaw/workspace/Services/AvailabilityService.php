<?php
namespace App\Services;

use App\Models\HotelRoomTypeInventory;
use App\Models\ProductOffer;

class AvailabilityService
{
    public function checkAvailability(int $productOfferId, int $hotelRoomTypeId, string $checkInDate, int $quantity = 1): array
    {
        $offer = ProductOffer::findOrFail($productOfferId);
        $nights = $offer->duration_nights;
        $requiredDates = [];
        
        for ($i = 0; $i < $nights; $i++) {
            $date = \Carbon\Carbon::parse($checkInDate)->addDays($i)->format('Y-m-d');
            $requiredDates[] = $date;
        }
        
        $errors = [];
        $available = true;
        
        foreach ($requiredDates as $date) {
            $inventory = HotelRoomTypeInventory::where('hotel_room_type_id', $hotelRoomTypeId)
                ->where('night_date', $date)
                ->first();
            
            if (!$inventory || $inventory->stop_sell) {
                $available = false;
                $errors[] = "No inventory available for {$date}";
                continue;
            }
            
            $availableUnits = $inventory->available_units - $inventory->held_units;
            if ($availableUnits < $quantity) {
                $available = false;
                $errors[] = "Only {$availableUnits} units available for {$date}";
            }
        }
        
        return [
            'available' => $available,
            'nights' => $requiredDates,
            'errors' => $errors,
        ];
    }
}
