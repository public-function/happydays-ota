<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
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

    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_COMPLETED = 'completed';

    public const REFERENCE_PREFIX = 'HBK';

    public function inventoryHold(): BelongsTo
    {
        return $this->belongsTo(InventoryHold::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BookingItem::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function generateReference(): string
    {
        $timestamp = now()->format('ymdHis');
        $random = strtoupper(substr(md5(uniqid()), 0, 4));
        return self::REFERENCE_PREFIX . $timestamp . $random;
    }

    public function cancel(): void
    {
        if ($this->status === self::STATUS_CANCELLED) {
            return;
        }

        $this->update(['status' => self::STATUS_CANCELLED]);

        // Restore inventory for each booking item
        foreach ($this->items as $item) {
            $this->restoreInventoryForItem($item);
        }
    }

    protected function restoreInventoryForItem(BookingItem $item): void
    {
        // Calculate nights in the stay
        $nights = $item->nights;
        $checkIn = $item->check_in_date;

        // Find or create inventory records for each night
        for ($i = 0; $i < $nights; $i++) {
            $nightDate = $checkIn->copy()->addDays($i);
            
            $inventory = Inventory::where('product_offer_id', $item->product_offer_id)
                ->where('hotel_room_type_id', $item->hotel_room_type_id)
                ->where('date', $nightDate)
                ->first();

            if ($inventory) {
                $inventory->increment('available_units', $item->quantity);
            }
        }
    }
}
