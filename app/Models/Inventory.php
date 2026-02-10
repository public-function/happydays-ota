<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventory extends Model
{
    protected $fillable = [
        'product_offer_id',
        'hotel_room_type_id',
        'date',
        'total_units',
        'available_units',
        'held_units',
        'stop_sell',
        'price',
    ];

    protected $casts = [
        'date' => 'date',
        'total_units' => 'integer',
        'available_units' => 'integer',
        'held_units' => 'integer',
        'stop_sell' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function productOffer(): BelongsTo
    {
        return $this->belongsTo(ProductOffer::class);
    }

    public function hotelRoomType(): BelongsTo
    {
        return $this->belongsTo(HotelRoomType::class);
    }

    public function holdItems(): HasMany
    {
        return $this->hasMany(InventoryHoldItem::class, 'inventory_id');
    }
}
