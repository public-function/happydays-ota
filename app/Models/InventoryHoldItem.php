<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryHoldItem extends Model
{
    protected $fillable = [
        'inventory_hold_id',
        'inventory_id',
        'night_date',
        'quantity',
    ];

    protected $casts = [
        'night_date' => 'date',
        'quantity' => 'integer',
    ];

    public function hold(): BelongsTo
    {
        return $this->belongsTo(InventoryHold::class, 'inventory_hold_id');
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }
}
