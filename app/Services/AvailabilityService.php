<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\HotelRoomType;
use App\Models\ProductOffer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AvailabilityService
{
    /**
     * Check if the requested quantity is available for given dates.
     */
    public function checkAvailability(
        ProductOffer $productOffer,
        HotelRoomType $hotelRoomType,
        int $quantity,
        int $nights
    ): bool {
        $startDate = now()->startOfDay();

        for ($i = 0; $i < $nights; $i++) {
            $checkDate = $startDate->copy()->addDays($i);

            if (!$this->isAvailableForDate($productOffer, $hotelRoomType, $checkDate, $quantity)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check availability for a specific date.
     */
    public function isAvailableForDate(
        ProductOffer $productOffer,
        HotelRoomType $hotelRoomType,
        \DateTimeInterface $date,
        int $quantity
    ): bool {
        $inventory = Inventory::where('product_offer_id', $productOffer->id)
            ->where('hotel_room_type_id', $hotelRoomType->id)
            ->where('date', $date)
            ->first();

        if (!$inventory) {
            return false;
        }

        if ($inventory->stop_sell) {
            return false;
        }

        return $inventory->available_units >= $quantity;
    }

    /**
     * Get available dates within a range.
     */
    public function getAvailableDates(
        ProductOffer $productOffer,
        HotelRoomType $hotelRoomType,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        int $quantity = 1
    ): Collection {
        $start = \Carbon\Carbon::parse($startDate)->startOfDay();
        $end = \Carbon\Carbon::parse($endDate)->endOfDay();

        $inventories = Inventory::where('product_offer_id', $productOffer->id)
            ->where('hotel_room_type_id', $hotelRoomType->id)
            ->whereBetween('date', [$start, $end])
            ->where('stop_sell', false)
            ->where('available_units', '>=', $quantity)
            ->orderBy('date')
            ->get();

        return $inventories->pluck('date');
    }

    /**
     * Get availability summary for a date range.
     */
    public function getAvailabilitySummary(
        ProductOffer $productOffer,
        HotelRoomType $hotelRoomType,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        int $quantity = 1
    ): array {
        $start = \Carbon\Carbon::parse($startDate)->startOfDay();
        $end = \Carbon\Carbon::parse($endDate)->endOfDay();

        $inventories = Inventory::where('product_offer_id', $productOffer->id)
            ->where('hotel_room_type_id', $hotelRoomType->id)
            ->whereBetween('date', [$start, $end])
            ->get();

        $availableDates = [];
        $unavailableDates = [];

        foreach ($inventories as $inventory) {
            if ($inventory->stop_sell || $inventory->available_units < $quantity) {
                $unavailableDates[] = $inventory->date->format('Y-m-d');
            } else {
                $availableDates[] = $inventory->date->format('Y-m-d');
            }
        }

        return [
            'available_dates' => $availableDates,
            'unavailable_dates' => $unavailableDates,
            'total_nights' => $inventories->count(),
            'available_nights' => count($availableDates),
        ];
    }

    /**
     * Check availability across multiple nights (consecutive dates).
     */
    public function checkAvailabilityForStay(
        ProductOffer $productOffer,
        HotelRoomType $hotelRoomType,
        \DateTimeInterface $checkInDate,
        int $nights,
        int $quantity = 1
    ): bool {
        $startDate = \Carbon\Carbon::parse($checkInDate)->startOfDay();

        for ($i = 0; $i < $nights; $i++) {
            $checkDate = $startDate->copy()->addDays($i);

            $inventory = Inventory::where('product_offer_id', $productOffer->id)
                ->where('hotel_room_type_id', $hotelRoomType->id)
                ->where('date', $checkDate)
                ->first();

            if (!$inventory) {
                return false;
            }

            if ($inventory->stop_sell) {
                return false;
            }

            if ($inventory->available_units < $quantity) {
                return false;
            }
        }

        return true;
    }

    /**
     * Calculate total available units across a date range.
     */
    public function getTotalAvailableUnits(
        ProductOffer $productOffer,
        HotelRoomType $hotelRoomType,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate
    ): int {
        $start = \Carbon\Carbon::parse($startDate)->startOfDay();
        $end = \Carbon\Carbon::parse($endDate)->endOfDay();

        $minUnits = Inventory::where('product_offer_id', $productOffer->id)
            ->where('hotel_room_type_id', $hotelRoomType->id)
            ->whereBetween('date', [$start, $end])
            ->where('stop_sell', false)
            ->min('available_units');

        return $minUnits ?? 0;
    }
}
