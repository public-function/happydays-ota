<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'reference',
        'inventory_hold_id',
        'customer_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'status',
        'total_amount',
        'paid_amount',
        'currency',
        'metadata',
        'confirmed_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'metadata' => 'array',
        'confirmed_at' => 'datetime',
    ];

    /**
     * Get the booking items.
     */
    public function items()
    {
        return $this->hasMany(BookingItem::class);
    }

    /**
     * Get the payments.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the inventory hold.
     */
    public function hold()
    {
        return $this->belongsTo(InventoryHold::class, 'inventory_hold_id');
    }

    /**
     * Check if booking can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED]);
    }

    /**
     * Mark booking as confirmed.
     */
    public function markAsConfirmed(): bool
    {
        $this->status = self::STATUS_CONFIRMED;
        $this->confirmed_at = now();
        return $this->save();
    }

    /**
     * Mark booking as cancelled.
     */
    public function markAsCancelled(): bool
    {
        $this->status = self::STATUS_CANCELLED;
        return $this->save();
    }

    /**
     * Mark booking as completed.
     */
    public function markAsCompleted(): bool
    {
        $this->status = self::STATUS_COMPLETED;
        return $this->save();
    }

    /**
     * Get remaining amount to be paid.
     */
    public function getRemainingAmountAttribute(): float
    {
        return max(0, $this->total_amount - $this->paid_amount);
    }
}
