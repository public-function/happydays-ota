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

    public function getEffectiveAvailableUnits(): int
    {
        return $this->available_units - $this->held_units;
    }

    public function hasAvailableUnits(int $quantity = 1): bool
    {
        return $this->getEffectiveAvailableUnits() >= $quantity && !$this->stop_sell;
    }
}
