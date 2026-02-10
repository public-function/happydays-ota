<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    protected $table = 'room_types';

    protected $fillable = [
        'code',
        'name',
        'description',
        'default_max_occupancy',
    ];

    protected $casts = [
        'default_max_occupancy' => 'integer',
    ];
}
