<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomType extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'default_max_occupancy',
    ];

    protected $casts = [
        'default_max_occupancy' => 'integer',
    ];

    public function hotelRoomTypes(): HasMany
    {
        return $this->hasMany(HotelRoomType::class);
    }
}
