<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Inventory;
use App\Models\InventoryHold;
use App\Models\InventoryHoldItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookingModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_has_many_items(): void
    {
        $booking = Booking::factory()->create();
        
        BookingItem::factory()->count(3)->create(['booking_id' => $booking->id]);

        $this->assertCount(3, $booking->items);
        $this->assertInstanceOf(BookingItem::class, $booking->items->first());
    }

    public function test_booking_has_many_items_through_relationship(): void
    {
        $booking = Booking::factory()->create();
        
        $item1 = BookingItem::factory()->create(['booking_id' => $booking->id]);
        $item2 = BookingItem::factory()->create(['booking_id' => $booking->id]);

        $items = $booking->items()->get();
        
        $this->assertCount(2, $items);
        $this->assertTrue($items->contains($item1));
        $this->assertTrue($items->contains($item2));
    }

    public function test_booking_belongs_to_inventory_hold(): void
    {
        $hold = InventoryHold::factory()->create();
        
        $booking = Booking::factory()->create([
            'inventory_hold_id' => $hold->id,
        ]);

        $this->assertInstanceOf(InventoryHold::class, $booking->inventoryHold);
        $this->assertEquals($hold->id, $booking->inventoryHold->id);
    }

    public function test_booking_generates_reference(): void
    {
        $booking = new Booking();
        $reference = $booking->generateReference();

        $this->assertStringStartsWith('HBK', $reference);
    }

    public function test_booking_cancelled_status(): void
    {
        $booking = Booking::factory()->create(['status' => Booking::STATUS_PENDING]);
        
        $booking->cancel();

        $this->assertEquals(Booking::STATUS_CANCELLED, $booking->fresh()->status);
    }
}
