<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelRoomTypeInventory extends Model
{
    protected $table = 'hotel_room_type_inventories';

    protected $fillable = [
        'hotel_room_type_id',
        'night_date',
        'available_units',
        'held_units',
        'stop_sell',
    ];

    protected $casts = [
        'night_date' => 'date',
        'available_units' => 'integer',
        'held_units' => 'integer',
        'stop_sell' => 'boolean',
    ];

    /**
     * Get effective available units (accounting for holds).
     */
    public function getEffectiveAvailableUnits(): int
    {
        return $this->available_units - $this->held_units;
    }

    /**
     * Check if quantity is available.
     */
    public function hasAvailableUnits(int $quantity = 1): bool
    {
        return $this->getEffectiveAvailableUnits() >= $quantity && !$this->stop_sell;
    }

    /**
     * Decrement held units.
     */
    public function decrementHeldUnits(int $quantity = 1): bool
    {
        if ($this->held_units >= $quantity) {
            $this->held_units -= $quantity;
            return $this->save();
        }
        return false;
    }

    /**
     * Increment held units.
     */
    public function incrementHeldUnits(int $quantity = 1): bool
    {
        $this->held_units += $quantity;
        return $this->save();
    }

    /**
     * Decrement available units.
     */
    public function decrementAvailableUnits(int $quantity = 1): bool
    {
        if ($this->available_units >= $quantity) {
            $this->available_units -= $quantity;
            return $this->save();
        }
        return false;
    }

    /**
     * Increment available units.
     */
    public function incrementAvailableUnits(int $quantity = 1): bool
    {
        $this->available_units += $quantity;
        return $this->save();
    }
}
