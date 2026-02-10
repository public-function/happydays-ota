<?php

namespace App\Services;

use App\Models\HotelRoomTypeInventory;
use App\Models\ProductOffer;
use Carbon\Carbon;

class AvailabilityService
{
    /**
     * Check availability for a product offer
     */
    public function checkAvailability(
        int $productOfferId,
        int $hotelRoomTypeId,
        string $checkInDate,
        int $quantity = 1
    ): array {
        $offer = ProductOffer::findOrFail($productOfferId);
        $nights = $offer->duration_nights;
        $requiredDates = [];
        
        for ($i = 0; $i < $nights; $i++) {
            $date = Carbon::parse($checkInDate)->addDays($i)->format('Y-m-d');
            $requiredDates[] = $date;
        }
        
        $errors = [];
        $available = true;
        $nightDetails = [];
        
        foreach ($requiredDates as $date) {
            $inventory = HotelRoomTypeInventory::where('hotel_room_type_id', $hotelRoomTypeId)
                ->where('night_date', $date)
                ->first();
            
            if (!$inventory) {
                $available = false;
                $errors[] = "No inventory record for {$date}";
                $nightDetails[] = ['date' => $date, 'available' => false, 'reason' => 'No inventory'];
                continue;
            }
            
            if ($inventory->stop_sell) {
                $available = false;
                $errors[] = "Stop sell for {$date}";
                $nightDetails[] = ['date' => $date, 'available' => false, 'reason' => 'Stop sell'];
                continue;
            }
            
            $availableUnits = $inventory->available_units - $inventory->held_units;
            if ($availableUnits < $quantity) {
                $available = false;
                $errors[] = "Only {$availableUnits}/{$quantity} units for {$date}";
                $nightDetails[] = ['date' => $date, 'available' => false, 'available_units' => $availableUnits, 'required' => $quantity];
                continue;
            }
            
            $nightDetails[] = ['date' => $date, 'available' => true, 'available_units' => $availableUnits];
        }
        
        return [
            'available' => $available,
            'nights' => $requiredDates,
            'night_details' => $nightDetails,
            'errors' => $errors,
        ];
    }

    /**
     * Get list of available dates within a range
     */
    public function getAvailableDates(
        int $productOfferId,
        int $hotelRoomTypeId,
        string $startDate,
        string $endDate,
        int $quantity = 1
    ): array {
        $availableDates = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        while ($current->lte($end)) {
            $checkIn = $current->format('Y-m-d');
            $availability = $this->checkAvailability($productOfferId, $hotelRoomTypeId, $checkIn, $quantity);
            
            if ($availability['available']) {
                $availableDates[] = $checkIn;
            }
            
            $current->addDay();
        }
        
        return $availableDates;
    }
}
