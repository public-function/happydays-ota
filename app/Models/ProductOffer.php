<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductOffer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'hotel_id',
        'rate_plan_id',
        'name',
        'duration_nights',
        'min_guests',
        'max_guests',
        'base_price',
        'status',
    ];

    protected $casts = [
        'duration_nights' => 'integer',
        'min_guests' => 'integer',
        'max_guests' => 'integer',
        'base_price' => 'decimal:2',
    ];

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function ratePlan(): BelongsTo
    {
        return $this->belongsTo(RatePlan::class);
    }

    public function hotelRoomTypes(): BelongsToMany
    {
        return $this->belongsToMany(HotelRoomType::class, 'hotel_room_type_product_offer')
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

    public function bookingItems(): HasMany
    {
        return $this->hasMany(BookingItem::class);
    }
}
