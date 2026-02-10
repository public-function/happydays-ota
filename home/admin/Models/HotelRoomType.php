<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HotelRoomType extends Model
{
    use SoftDeletes;

    protected $table = 'hotel_room_types';

    protected $fillable = [
        'hotel_id',
        'supplier_code',
        'supplier_name',
        'max_occupancy',
        'min_occupancy',
        'status',
    ];

    protected $casts = [
        'max_occupancy' => 'integer',
        'min_occupancy' => 'integer',
        'status' => 'string',
    ];

    /**
     * Get the hotel that owns this room type.
     */
    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Get inventories for this room type.
     */
    public function inventories()
    {
        return $this->hasMany(HotelRoomTypeInventory::class, 'hotel_room_type_id');
    }
}
