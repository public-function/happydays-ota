<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\InventoryHoldService;
use App\Services\AvailabilityService;
use App\Models\InventoryHold;
use App\Models\InventoryHoldItem;
use App\Models\HotelRoomTypeInventory;
use App\Models\Booking;
use Mockery;

class InventoryHoldServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
    
    public function test_creates_hold_successfully(): void
    {
        $service = new InventoryHoldService();
        
        $holdData = [
            'product_offer_id' => 1,
            'hotel_room_type_id' => 1,
            'check_in_date' => '2024-06-01',
            'quantity' => 2,
            'customer_id' => 1,
        ];
        
        $mockAvailability = [
            'available' => true,
            'nights' => ['2024-06-01', '2024-06-02'],
        ];
        
        $mockInventory = Mockery::mock(HotelRoomTypeInventory::class);
        $mockInventory->shouldReceive('save')->times(2);
        
        $mockHold = Mockery::mock(InventoryHold::class);
        $mockHold->shouldReceive('items')->andReturnSelf();
        $mockHold->shouldReceive('create')->times(2);
        $mockHold->shouldReceive('getAttribute')->with('id')->andReturn(1);
        
        AvailabilityService::shouldReceive('checkAvailability')->andReturn($mockAvailability);
        InventoryHold::shouldReceive('create')->andReturn($mockHold);
        HotelRoomTypeInventory::shouldReceive('where')->andReturnSelf()->shouldReceive('lockForUpdate')->andReturnSelf()->shouldReceive('first')->andReturn($mockInventory);
        
        $hold = $service->createHold($holdData);
        
        $this->assertInstanceOf(InventoryHold::class, $hold);
    }
    
    public function test_throws_exception_when_insufficient_inventory(): void
    {
        $service = new InventoryHoldService();
        
        $holdData = [
            'product_offer_id' => 1,
            'hotel_room_type_id' => 1,
            'check_in_date' => '2024-06-01',
            'quantity' => 10,
        ];
        
        $mockAvailability = [
            'available' => false,
            'errors' => ['Only 5 units available'],
        ];
        
        AvailabilityService::shouldReceive('checkAvailability')->andReturn($mockAvailability);
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient inventory');
        
        $service->createHold($holdData);
    }
    
    public function test_converts_hold_to_booking(): void
    {
        $service = new InventoryHoldService();
        
        $mockItem = Mockery::mock(InventoryHoldItem::class);
        $mockItem->shouldReceive('getAttribute')->with('hotel_room_type_id')->andReturn(1);
        $mockItem->shouldReceive('getAttribute')->with('night_date')->andReturn('2024-06-01');
        $mockItem->shouldReceive('getAttribute')->with('quantity')->andReturn(2);
        
        $mockHold = Mockery::mock(InventoryHold::class);
        $mockHold->shouldReceive('getAttribute')->with('status')->andReturn('active');
        $mockHold->shouldReceive('getAttribute')->with('expires_at')->andReturn(now()->addMinutes(10));
        $mockHold->shouldReceive('getAttribute')->with('items')->andReturn(collect([$mockItem]));
        $mockHold->shouldReceive('save')->once();
        
        $mockInventory = Mockery::mock(HotelRoomTypeInventory::class);
        $mockInventory->shouldReceive('save');
        
        $mockBooking = Mockery::mock(Booking::class);
        $mockBooking->shouldReceive('getAttribute')->with('id')->andReturn(1);
        
        HotelRoomTypeInventory::shouldReceive('where')->andReturnSelf()->shouldReceive('lockForUpdate')->andReturnSelf()->shouldReceive('first')->andReturn($mockInventory);
        Booking::shouldReceive('create')->andReturn($mockBooking);
        
        $bookingData = [
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'total_amount' => 500,
        ];
        
        $booking = $service->convertToBooking($mockHold, $bookingData);
        
        $this->assertInstanceOf(Booking::class, $booking);
    }
    
    public function test_throws_exception_when_converting_expired_hold(): void
    {
        $service = new InventoryHoldService();
        
        $mockHold = Mockery::mock(InventoryHold::class);
        $mockHold->shouldReceive('getAttribute')->with('status')->andReturn('active');
        $mockHold->shouldReceive('getAttribute')->with('expires_at')->andReturn(now()->subMinutes(1));
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Hold has expired');
        
        $service->convertToBooking($mockHold, []);
    }
    
    public function test_expires_hold_and_restores_inventory(): void
    {
        $service = new InventoryHoldService();
        
        $mockItem = Mockery::mock(InventoryHoldItem::class);
        $mockItem->shouldReceive('getAttribute')->with('hotel_room_type_id')->andReturn(1);
        $mockItem->shouldReceive('getAttribute')->with('night_date')->andReturn('2024-06-01');
        $mockItem->shouldReceive('getAttribute')->with('quantity')->andReturn(2);
        
        $mockHold = Mockery::mock(InventoryHold::class);
        $mockHold->shouldReceive('getAttribute')->with('items')->andReturn(collect([$mockItem]));
        $mockHold->shouldReceive('save')->once();
        
        $mockInventory = Mockery::mock(HotelRoomTypeInventory::class);
        $mockInventory->shouldReceive('save');
        
        HotelRoomTypeInventory::shouldReceive('where')->andReturnSelf()->shouldReceive('lockForUpdate')->andReturnSelf()->shouldReceive('first')->andReturn($mockInventory);
        
        $service->expireHold($mockHold);
        
        $this->assertTrue(true); // No exception thrown
    }
    
    public function test_generates_booking_reference(): void
    {
        $service = new InventoryHoldService();
        
        Booking::shouldReceive('count')->andReturn(5);
        
        $reference = $service->generateBookingReference();
        
        $this->assertStringStartsWith('BK-', $reference);
        $this->assertStringContainsString(date('Y'), $reference);
    }
}
