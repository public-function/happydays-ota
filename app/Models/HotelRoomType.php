<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HotelRoomType extends Model
{
    use SoftDeletes;

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
    ];

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function productOffers(): BelongsToMany
    {
        return $this->belongsToMany(ProductOffer::class, 'hotel_room_type_product_offer')
                    ->withPivot(['is_default', 'price_delta_amount'])
                    ->withTimestamps();
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function inventoryHolds(): HasMany
    {
        return $this->hasMany(InventoryHold::class);
    }
}
