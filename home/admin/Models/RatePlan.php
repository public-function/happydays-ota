<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RatePlan extends Model
{
    protected $table = 'rate_plans';

    protected $fillable = [
        'name',
        'board_type',
        'cancellation_policy',
        'status',
    ];

    protected $casts = [
        'cancellation_policy' => 'array',
        'status' => 'string',
        'board_type' => 'string',
    ];

    /**
     * Get product offers for this rate plan.
     */
    public function productOffers()
    {
        return $this->hasMany(ProductOffer::class);
    }
}
