<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    /**
     * Get the inventory hold associated with this item.
     */
    public function hold()
    {
        return $this->belongsTo(InventoryHold::class, 'inventory_hold_id');
    }

    /**
     * Get the inventory record.
     */
    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
}
