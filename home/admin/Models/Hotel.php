<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hotel extends Model
{
    use SoftDeletes;

    protected $table = 'hotels';

    protected $fillable = [
        'name',
        'address',
        'city',
        'country',
        'postal_code',
        'phone',
        'email',
        'website',
        'status',
        'checkin_time',
        'checkout_time',
        'timezone',
    ];

    protected $casts = [
        'status' => 'string',
        'checkin_time' => 'datetime:H:i:s',
        'checkout_time' => 'datetime:H:i:s',
    ];

    /**
     * Get room types for this hotel.
     */
    public function roomTypes()
    {
        return $this->hasMany(HotelRoomType::class);
    }

    /**
     * Get product offers for this hotel.
     */
    public function productOffers()
    {
        return $this->hasMany(ProductOffer::class);
    }
}
