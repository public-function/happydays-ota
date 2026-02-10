<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_REFUNDED = 'refunded';

    public const METHOD_CREDIT_CARD = 'credit_card';
    public const METHOD_BANK_TRANSFER = 'bank_transfer';
    public const METHOD_CASH = 'cash';
    public const METHOD_OTHER = 'other';

    protected $fillable = [
        'booking_id',
        'reference',
        'amount',
        'currency',
        'method',
        'status',
        'transaction_data',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_data' => 'array',
    ];

    /**
     * Get the booking.
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Check if payment is successful.
     */
    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Mark payment as completed.
     */
    public function markAsCompleted(): bool
    {
        $this->status = self::STATUS_COMPLETED;
        return $this->save();
    }

    /**
     * Mark payment as failed.
     */
    public function markAsFailed(): bool
    {
        $this->status = self::STATUS_FAILED;
        return $this->save();
    }

    /**
     * Mark payment as refunded.
     */
    public function markAsRefunded(): bool
    {
        $this->status = self::STATUS_REFUNDED;
        return $this->save();
    }
}
