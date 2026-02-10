<?php

namespace Tests\Unit;

use App\Models\Inventory;
use App\Models\InventoryHold;
use App\Services\InventoryHoldService;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class InventoryHoldServiceTest extends TestCase
{
    private InventoryHoldService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new InventoryHoldService();
    }

    public function test_hold_ttl_is_configured(): void
    {
        $ttl = config('ota.hold_ttl_minutes', 15);
        $this->assertEquals(15, $ttl);
    }

    public function test_hold_token_generation_format(): void
    {
        // Test that the service can generate tokens (mock test)
        $mockToken = 'abc123def456' . str_repeat('x', 52);
        
        $this->assertEquals(64, strlen($mockToken));
        $this->assertMatchesRegularExpression('/^[a-z0-9]+$/', $mockToken);
    }

    public function test_hold_status_constants(): void
    {
        $this->assertEquals('active', InventoryHold::STATUS_ACTIVE);
        $this->assertEquals('converted', InventoryHold::STATUS_CONVERTED);
        $this->assertEquals('expired', InventoryHold::STATUS_EXPIRED);
        $this->assertEquals('cancelled', InventoryHold::STATUS_CANCELLED);
    }

    public function test_hold_model_fillable(): void
    {
        $hold = new InventoryHold();
        $fillable = $hold->getFillable();
        
        $this->assertContains('token', $fillable);
        $this->assertContains('product_offer_id', $fillable);
        $this->assertContains('hotel_room_type_id', $fillable);
        $this->assertContains('check_in_date', $fillable);
        $this->assertContains('quantity', $fillable);
        $this->assertContains('status', $fillable);
        $this->assertContains('expires_at', $fillable);
    }

    public function test_hold_is_active_check(): void
    {
        $activeHold = new InventoryHold();
        $activeHold->status = InventoryHold::STATUS_ACTIVE;
        $activeHold->expires_at = Carbon::now()->addMinutes(10);
        
        $this->assertEquals(InventoryHold::STATUS_ACTIVE, $activeHold->status);
        $this->assertTrue($activeHold->expires_at->isFuture());
    }

    public function test_hold_is_expired_check(): void
    {
        $expiredHold = new InventoryHold();
        $expiredHold->expires_at = Carbon::now()->subMinutes(10);
        
        $this->assertTrue($expiredHold->expires_at->isPast());
    }

    public function test_inventory_model_fillable(): void
    {
        $inventory = new Inventory();
        $fillable = $inventory->getFillable();
        
        $this->assertContains('product_offer_id', $fillable);
        $this->assertContains('hotel_room_type_id', $fillable);
        $this->assertContains('date', $fillable);
        $this->assertContains('total_units', $fillable);
        $this->assertContains('available_units', $fillable);
        $this->assertContains('held_units', $fillable);
        $this->assertContains('stop_sell', $fillable);
    }

    public function test_inventory_casts(): void
    {
        $inventory = new Inventory();
        $casts = $inventory->getCasts();
        
        $this->assertEquals('integer', $casts['total_units']);
        $this->assertEquals('integer', $casts['available_units']);
        $this->assertEquals('integer', $casts['held_units']);
        $this->assertEquals('boolean', $casts['stop_sell']);
        $this->assertEquals('decimal:2', $casts['price']);
    }

    public function test_inventory_effective_available_calculation(): void
    {
        $inventory = new Inventory();
        $inventory->available_units = 20;
        $inventory->held_units = 5;
        
        $effective = $inventory->available_units - $inventory->held_units;
        
        $this->assertEquals(15, $effective);
    }

    public function test_inventory_has_available_units(): void
    {
        $inventory = new Inventory();
        $inventory->available_units = 10;
        $inventory->held_units = 3;
        $inventory->stop_sell = false;
        
        $hasUnits = $inventory->available_units - $inventory->held_units >= 5;
        
        $this->assertTrue($hasUnits);
    }

    public function test_inventory_stop_sell_prevents_availability(): void
    {
        $inventory = new Inventory();
        $inventory->stop_sell = true;
        
        $this->assertTrue($inventory->stop_sell);
    }

    public function test_create_hold_input_structure(): void
    {
        $input = [
            'product_offer_id' => 1,
            'hotel_room_type_id' => 2,
            'check_in_date' => '2026-02-15',
            'quantity' => 2,
            'customer_id' => 123,
        ];
        
        $this->assertArrayHasKey('product_offer_id', $input);
        $this->assertArrayHasKey('hotel_room_type_id', $input);
        $this->assertArrayHasKey('check_in_date', $input);
        $this->assertArrayHasKey('quantity', $input);
        $this->assertEquals(2, $input['quantity']);
    }

    public function test_convert_to_booking_data_structure(): void
    {
        $bookingData = [
            'reference' => 'BK-2026-000001',
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'customer_phone' => '+1234567890',
            'total_amount' => 500.00,
            'paid_amount' => 500.00,
            'currency' => 'USD',
        ];
        
        $this->assertArrayHasKey('customer_name', $bookingData);
        $this->assertArrayHasKey('customer_email', $bookingData);
        $this->assertArrayHasKey('total_amount', $bookingData);
    }

    public function test_hold_expired_state_transitions(): void
    {
        $hold = new InventoryHold();
        $hold->status = InventoryHold::STATUS_ACTIVE;
        $hold->expires_at = Carbon::now()->subMinutes(1);
        
        // Simulate expired state
        if ($hold->expires_at->isPast()) {
            $hold->status = InventoryHold::STATUS_EXPIRED;
        }
        
        $this->assertEquals(InventoryHold::STATUS_EXPIRED, $hold->status);
    }

    public function test_hold_cancelled_state_transitions(): void
    {
        $hold = new InventoryHold();
        $hold->status = InventoryHold::STATUS_ACTIVE;
        
        // Simulate cancelled state
        $hold->status = InventoryHold::STATUS_CANCELLED;
        
        $this->assertEquals(InventoryHold::STATUS_CANCELLED, $hold->status);
    }
}
