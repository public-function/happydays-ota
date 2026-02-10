<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingItem extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';

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

    /**
     * Get the booking.
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Check if item can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED]);
    }

    /**
     * Mark item as cancelled.
     */
    public function markAsCancelled(): bool
    {
        $this->status = self::STATUS_CANCELLED;
        return $this->save();
    }

    /**
     * Calculate total price.
     */
    public function calculateTotalPrice(): float
    {
        return $this->unit_price * $this->nights * $this->quantity;
    }
}
