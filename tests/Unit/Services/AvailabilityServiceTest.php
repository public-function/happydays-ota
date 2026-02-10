<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\AvailabilityService;
use App\Models\ProductOffer;
use App\Models\HotelRoomTypeInventory;
use Mockery;

class AvailabilityServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
    
    public function test_returns_available_when_inventory_exists(): void
    {
        $service = new AvailabilityService();
        
        $offer = Mockery::mock(ProductOffer::class);
        $offer->shouldReceive('getAttribute')->with('duration_nights')->andReturn(2);
        
        $inventory = Mockery::mock(HotelRoomTypeInventory::class);
        $inventory->shouldReceive('getAttribute')->with('stop_sell')->andReturn(false);
        $inventory->shouldReceive('getAttribute')->with('available_units')->andReturn(5);
        $inventory->shouldReceive('getAttribute')->with('held_units')->andReturn(1);
        
        ProductOffer::shouldReceive('findOrFail')->with(1)->andReturn($offer);
        HotelRoomTypeInventory::shouldReceive('where')
            ->with('hotel_room_type_id', 1)
            ->andReturnSelf()
            ->shouldReceive('first')->andReturn($inventory);
        
        $result = $service->checkAvailability(1, 1, '2024-06-01', 1);
        
        $this->assertTrue($result['available']);
        $this->assertEmpty($result['errors']);
        $this->assertCount(2, $result['nights']);
    }
    
    public function test_returns_unavailable_when_inventory_missing(): void
    {
        $service = new AvailabilityService();
        
        $offer = Mockery::mock(ProductOffer::class);
        $offer->shouldReceive('getAttribute')->with('duration_nights')->andReturn(1);
        
        ProductOffer::shouldReceive('findOrFail')->with(1)->andReturn($offer);
        HotelRoomTypeInventory::shouldReceive('where')
            ->andReturnSelf()
            ->shouldReceive('first')->andReturn(null);
        
        $result = $service->checkAvailability(1, 1, '2024-06-01', 1);
        
        $this->assertFalse($result['available']);
        $this->assertNotEmpty($result['errors']);
    }
    
    public function test_returns_unavailable_when_stop_sell_is_true(): void
    {
        $service = new AvailabilityService();
        
        $offer = Mockery::mock(ProductOffer::class);
        $offer->shouldReceive('getAttribute')->with('duration_nights')->andReturn(1);
        
        $inventory = Mockery::mock(HotelRoomTypeInventory::class);
        $inventory->shouldReceive('getAttribute')->with('stop_sell')->andReturn(true);
        
        ProductOffer::shouldReceive('findOrFail')->with(1)->andReturn($offer);
        HotelRoomTypeInventory::shouldReceive('where')
            ->andReturnSelf()
            ->shouldReceive('first')->andReturn($inventory);
        
        $result = $service->checkAvailability(1, 1, '2024-06-01', 1);
        
        $this->assertFalse($result['available']);
        $this->assertStringContainsString('No inventory available', $result['errors'][0]);
    }
    
    public function test_returns_unavailable_when_insufficient_units(): void
    {
        $service = new AvailabilityService();
        
        $offer = Mockery::mock(ProductOffer::class);
        $offer->shouldReceive('getAttribute')->with('duration_nights')->andReturn(1);
        
        $inventory = Mockery::mock(HotelRoomTypeInventory::class);
        $inventory->shouldReceive('getAttribute')->with('stop_sell')->andReturn(false);
        $inventory->shouldReceive('getAttribute')->with('available_units')->andReturn(2);
        $inventory->shouldReceive('getAttribute')->with('held_units')->andReturn(2);
        
        ProductOffer::shouldReceive('findOrFail')->with(1)->andReturn($offer);
        HotelRoomTypeInventory::shouldReceive('where')
            ->andReturnSelf()
            ->shouldReceive('first')->andReturn($inventory);
        
        $result = $service->checkAvailability(1, 1, '2024-06-01', 1);
        
        $this->assertFalse($result['available']);
        $this->assertStringContainsString('Only 0 units available', $result['errors'][0]);
    }
    
    public function test_generates_correct_dates_for_multi_night_stay(): void
    {
        $service = new AvailabilityService();
        
        $offer = Mockery::mock(ProductOffer::class);
        $offer->shouldReceive('getAttribute')->with('duration_nights')->andReturn(3);
        
        $inventory = Mockery::mock(HotelRoomTypeInventory::class);
        $inventory->shouldReceive('getAttribute')->with('stop_sell')->andReturn(false);
        $inventory->shouldReceive('getAttribute')->with('available_units')->andReturn(10);
        $inventory->shouldReceive('getAttribute')->with('held_units')->andReturn(0);
        
        ProductOffer::shouldReceive('findOrFail')->with(1)->andReturn($offer);
        HotelRoomTypeInventory::shouldReceive('where')
            ->andReturnSelf()
            ->shouldReceive('first')->andReturn($inventory);
        
        $result = $service->checkAvailability(1, 1, '2024-06-15', 1);
        
        $this->assertCount(3, $result['nights']);
        $this->assertEquals('2024-06-15', $result['nights'][0]);
        $this->assertEquals('2024-06-16', $result['nights'][1]);
        $this->assertEquals('2024-06-17', $result['nights'][2]);
    }
    
    public function test_respects_quantity_parameter(): void
    {
        $service = new AvailabilityService();
        
        $offer = Mockery::mock(ProductOffer::class);
        $offer->shouldReceive('getAttribute')->with('duration_nights')->andReturn(1);
        
        $inventory = Mockery::mock(HotelRoomTypeInventory::class);
        $inventory->shouldReceive('getAttribute')->with('stop_sell')->andReturn(false);
        $inventory->shouldReceive('getAttribute')->with('available_units')->andReturn(5);
        $inventory->shouldReceive('getAttribute')->with('held_units')->andReturn(0);
        
        ProductOffer::shouldReceive('findOrFail')->with(1)->andReturn($offer);
        HotelRoomTypeInventory::shouldReceive('where')
            ->andReturnSelf()
            ->shouldReceive('first')->andReturn($inventory);
        
        $result = $service->checkAvailability(1, 1, '2024-06-01', 5);
        
        $this->assertTrue($result['available']);
    }
}
