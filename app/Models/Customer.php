<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'country',
        'language',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function holds(): HasMany
    {
        return $this->hasMany(InventoryHold::class);
    }
}
