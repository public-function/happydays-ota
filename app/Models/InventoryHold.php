<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryHold extends Model
{
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
        'quantity' => 'integer',
        'expires_at' => 'datetime',
        'converted_at' => 'datetime',
    ];

    public const STATUS_ACTIVE = 'active';
    public const STATUS_CONVERTED = 'converted';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';

    public const HOLD_DURATION_MINUTES = 15;

    public function productOffer(): BelongsTo
    {
        return $this->belongsTo(ProductOffer::class);
    }

    public function hotelRoomType(): BelongsTo
    {
        return $this->belongsTo(HotelRoomType::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function holdItems(): HasMany
    {
        return $this->hasMany(InventoryHoldItem::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE 
            && $this->expires_at->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED 
            || $this->expires_at->isPast();
    }

    public function convertToBooking(): ?Booking
    {
        if (!$this->isActive()) {
            return null;
        }

        $booking = Booking::create([
            'inventory_hold_id' => $this->id,
            'customer_name' => 'Pending Customer',
            'customer_email' => 'pending@example.com',
            'status' => Booking::STATUS_PENDING,
        ]);

        $this->update([
            'status' => self::STATUS_CONVERTED,
            'converted_at' => now(),
        ]);

        return $booking;
    }

    public function release(): void
    {
        foreach ($this->holdItems as $holdItem) {
            $inventory = $holdItem->inventory;
            if ($inventory) {
                $inventory->decrement('held_units', $holdItem->quantity);
            }
        }
        $this->update(['status' => self::STATUS_CANCELLED]);
    }
}
