<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingItem extends Model
{
    protected $fillable = [
        'booking_id',
        'product_offer_id',
        'hotel_room_type_id',
        'check_in_date',
        'nights',
        'quantity',
        'unit_price',
        'total_price',
        'status',
        'snapshot',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'nights' => 'integer',
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'snapshot' => 'array',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function productOffer(): BelongsTo
    {
        return $this->belongsTo(ProductOffer::class);
    }

    public function hotelRoomType(): BelongsTo
    {
        return $this->belongsTo(HotelRoomType::class);
    }
}
