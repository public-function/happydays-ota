<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RatePlan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'cancellation_policy',
        'status',
    ];

    protected $casts = [
        'cancellation_policy' => 'array',
    ];

    public function productOffers(): HasMany
    {
        return $this->hasMany(ProductOffer::class);
    }
}
