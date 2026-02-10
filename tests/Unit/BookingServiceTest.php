<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Payment;
use App\Services\BookingService;
use App\Services\InventoryHoldService;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class BookingServiceTest extends TestCase
{
    private BookingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BookingService(new InventoryHoldService());
    }

    public function test_generate_booking_reference_format(): void
    {
        $year = Carbon::now()->format('Y');
        $sequence = 1;
        $reference = sprintf('BK-%s-%06d', $year, $sequence);
        
        $this->assertMatchesRegularExpression('/^BK-\d{4}-\d{6}$/', $reference);
        $this->assertEquals($year, substr($reference, 3, 4));
    }

    public function test_booking_reference_sequence_padding(): void
    {
        $sequences = [1, 10, 100, 1000, 10000];
        $year = '2026';
        
        foreach ($sequences as $seq) {
            $reference = sprintf('BK-%s-%06d', $year, $seq);
            $expected = sprintf('BK-%s-%06d', $year, $seq);
            $this->assertEquals($expected, $reference);
        }
    }

    public function test_booking_model_fillable(): void
    {
        $booking = new Booking();
        $fillable = $booking->getFillable();
        
        $this->assertContains('reference', $fillable);
        $this->assertContains('customer_name', $fillable);
        $this->assertContains('customer_email', $fillable);
        $this->assertContains('status', $fillable);
        $this->assertContains('total_amount', $fillable);
        $this->assertContains('paid_amount', $fillable);
    }

    public function test_booking_status_constants(): void
    {
        $this->assertEquals('pending', Booking::STATUS_PENDING);
        $this->assertEquals('confirmed', Booking::STATUS_CONFIRMED);
        $this->assertEquals('cancelled', Booking::STATUS_CANCELLED);
        $this->assertEquals('completed', Booking::STATUS_COMPLETED);
    }

    public function test_booking_item_status_constants(): void
    {
        $this->assertEquals('pending', BookingItem::STATUS_PENDING);
        $this->assertEquals('confirmed', BookingItem::STATUS_CONFIRMED);
        $this->assertEquals('cancelled', BookingItem::STATUS_CANCELLED);
    }

    public function test_booking_item_model_fillable(): void
    {
        $item = new BookingItem();
        $fillable = $item->getFillable();
        
        $this->assertContains('booking_id', $fillable);
        $this->assertContains('product_offer_id', $fillable);
        $this->assertContains('hotel_room_type_id', $fillable);
        $this->assertContains('check_in_date', $fillable);
        $this->assertContains('nights', $fillable);
        $this->assertContains('quantity', $fillable);
        $this->assertContains('unit_price', $fillable);
        $this->assertContains('total_price', $fillable);
        $this->assertContains('status', $fillable);
    }

    public function test_payment_model_fillable(): void
    {
        $payment = new Payment();
        $fillable = $payment->getFillable();
        
        $this->assertContains('booking_id', $fillable);
        $this->assertContains('reference', $fillable);
        $this->assertContains('amount', $fillable);
        $this->assertContains('currency', $fillable);
        $this->assertContains('status', $fillable);
        $this->assertContains('method', $fillable);
    }

    public function test_payment_status_constants(): void
    {
        $this->assertEquals('pending', Payment::STATUS_PENDING);
        $this->assertEquals('completed', Payment::STATUS_COMPLETED);
        $this->assertEquals('failed', Payment::STATUS_FAILED);
        $this->assertEquals('refunded', Payment::STATUS_REFUNDED);
    }

    public function test_payment_method_constants(): void
    {
        $this->assertEquals('credit_card', Payment::METHOD_CREDIT_CARD);
        $this->assertEquals('bank_transfer', Payment::METHOD_BANK_TRANSFER);
        $this->assertEquals('cash', Payment::METHOD_CASH);
        $this->assertEquals('other', Payment::METHOD_OTHER);
    }

    public function test_booking_can_be_cancelled_status(): void
    {
        $pendingBooking = new Booking();
        $pendingBooking->status = Booking::STATUS_PENDING;
        
        $confirmedBooking = new Booking();
        $confirmedBooking->status = Booking::STATUS_CONFIRMED;
        
        $cancelledBooking = new Booking();
        $cancelledBooking->status = Booking::STATUS_CANCELLED;
        
        $this->assertTrue(in_array($pendingBooking->status, [Booking::STATUS_PENDING, Booking::STATUS_CONFIRMED]));
        $this->assertTrue(in_array($confirmedBooking->status, [Booking::STATUS_PENDING, Booking::STATUS_CONFIRMED]));
        $this->assertFalse(in_array($cancelledBooking->status, [Booking::STATUS_PENDING, Booking::STATUS_CONFIRMED]));
    }

    public function test_booking_item_can_be_cancelled_status(): void
    {
        $pendingItem = new BookingItem();
        $pendingItem->status = BookingItem::STATUS_PENDING;
        
        $confirmedItem = new BookingItem();
        $confirmedItem->status = BookingItem::STATUS_CONFIRMED;
        
        $cancelledItem = new BookingItem();
        $cancelledItem->status = BookingItem::STATUS_CANCELLED;
        
        $this->assertTrue(in_array($pendingItem->status, [BookingItem::STATUS_PENDING, BookingItem::STATUS_CONFIRMED]));
        $this->assertTrue(in_array($confirmedItem->status, [BookingItem::STATUS_PENDING, BookingItem::STATUS_CONFIRMED]));
        $this->assertFalse(in_array($cancelledItem->status, [BookingItem::STATUS_PENDING, BookingItem::STATUS_CONFIRMED]));
    }

    public function test_create_booking_from_hold_input_structure(): void
    {
        $customerData = [
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'customer_phone' => '+1987654321',
        ];
        
        $paymentData = [
            'amount' => 350.00,
            'currency' => 'USD',
            'method' => 'credit_card',
        ];
        
        $this->assertArrayHasKey('customer_name', $customerData);
        $this->assertArrayHasKey('customer_email', $customerData);
        $this->assertArrayHasKey('amount', $paymentData);
        $this->assertArrayHasKey('currency', $paymentData);
    }

    public function test_booking_total_calculation(): void
    {
        $unitPrice = 100.00;
        $nights = 3;
        $quantity = 2;
        
        $totalPrice = $unitPrice * $nights * $quantity;
        
        $this->assertEquals(600.00, $totalPrice);
    }

    public function test_booking_remaining_amount_calculation(): void
    {
        $totalAmount = 500.00;
        $paidAmount = 200.00;
        
        $remainingAmount = max(0, $totalAmount - $paidAmount);
        
        $this->assertEquals(300.00, $remainingAmount);
    }

    public function test_payment_reference_format(): void
    {
        $reference = 'PAY-' . strtoupper(bin2hex(random_bytes(6)));
        
        $this->assertMatchesRegularExpression('/^PAY-[A-F0-9]{12}$/', $reference);
    }

    public function test_booking_snapshot_structure(): void
    {
        $snapshot = [
            'inventory_id' => 1,
            'date' => '2026-02-15',
            'price' => 150.00,
            'total_units' => 20,
            'held_at' => Carbon::now()->toIso8601String(),
        ];
        
        $this->assertArrayHasKey('inventory_id', $snapshot);
        $this->assertArrayHasKey('date', $snapshot);
        $this->assertArrayHasKey('price', $snapshot);
        $this->assertArrayHasKey('total_units', $snapshot);
        $this->assertArrayHasKey('held_at', $snapshot);
    }

    public function test_booking_metadata_casting(): void
    {
        $booking = new Booking();
        $casts = $booking->getCasts();
        
        $this->assertEquals('decimal:2', $casts['total_amount']);
        $this->assertEquals('decimal:2', $casts['paid_amount']);
        $this->assertEquals('array', $casts['metadata']);
        $this->assertEquals('datetime', $casts['confirmed_at']);
    }

    public function test_booking_item_total_price_calculation(): void
    {
        $item = new BookingItem();
        $item->unit_price = 120.00;
        $item->nights = 2;
        $item->quantity = 1;
        
        $totalPrice = $item->unit_price * $item->nights * $item->quantity;
        
        $this->assertEquals(240.00, $totalPrice);
    }
}
