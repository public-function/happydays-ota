<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryHold extends Model
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_CONVERTED = 'converted';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'token',
        'product_offer_id',
        'hotel_room_type_id',
        'check_in_date',
        'quantity',
        'customer_id',
        'status',
        'expires_at',
        'converted_at',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'expires_at' => 'datetime',
        'converted_at' => 'datetime',
        'quantity' => 'integer',
    ];

    /**
     * Get the hold items associated with this hold.
     */
    public function holdItems()
    {
        return $this->hasMany(InventoryHoldItem::class);
    }

    /**
     * Check if hold is still active.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE 
            && $this->expires_at->isFuture();
    }

    /**
     * Check if hold is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Get the number of nights for this hold.
     */
    public function getNightsCountAttribute(): int
    {
        return $this->holdItems->count();
    }

    /**
     * Mark hold as converted.
     */
    public function markAsConverted(): bool
    {
        $this->status = self::STATUS_CONVERTED;
        $this->converted_at = now();
        return $this->save();
    }

    /**
     * Mark hold as expired.
     */
    public function markAsExpired(): bool
    {
        $this->status = self::STATUS_EXPIRED;
        return $this->save();
    }

    /**
     * Mark hold as cancelled.
     */
    public function markAsCancelled(): bool
    {
        $this->status = self::STATUS_CANCELLED;
        return $this->save();
    }
}
