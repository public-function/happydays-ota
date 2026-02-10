<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductOffer extends Model
{
    use SoftDeletes;

    protected $table = 'product_offers';

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
        'status' => 'string',
    ];

    /**
     * Get the hotel that owns this offer.
     */
    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Get the rate plan for this offer.
     */
    public function ratePlan()
    {
        return $this->belongsTo(RatePlan::class);
    }

    /**
     * Get booking items for this offer.
     */
    public function bookingItems()
    {
        return $this->hasMany(BookingItem::class);
    }
}
