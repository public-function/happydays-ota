<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\InventoryHold;
use App\Models\InventoryHoldItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class InventoryHoldModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_hold_belongs_to_product_offer(): void
    {
        $hold = InventoryHold::factory()->create();

        $this->assertInstanceOf(\App\Models\ProductOffer::class, $hold->productOffer);
    }

    public function test_inventory_hold_belongs_to_hotel_room_type(): void
    {
        $hold = InventoryHold::factory()->create();

        $this->assertInstanceOf(\App\Models\HotelRoomType::class, $hold->hotelRoomType);
    }

    public function test_inventory_hold_has_many_hold_items(): void
    {
        $hold = InventoryHold::factory()->create();
        
        InventoryHoldItem::factory()->count(3)->create(['inventory_hold_id' => $hold->id]);

        $this->assertCount(3, $hold->holdItems);
    }

    public function test_inventory_hold_is_active(): void
    {
        $hold = InventoryHold::factory()->create([
            'status' => 'active',
            'expires_at' => now()->addMinutes(15),
        ]);

        $this->assertTrue($hold->isActive());
    }

    public function test_inventory_hold_is_expired(): void
    {
        $hold = InventoryHold::factory()->expired()->create();

        $this->assertTrue($hold->isExpired());
    }

    public function test_inventory_hold_not_active_when_converted(): void
    {
        $hold = InventoryHold::factory()->converted()->create();

        $this->assertFalse($hold->isActive());
    }

    public function test_inventory_hold_release(): void
    {
        $hold = InventoryHold::factory()->create([
            'status' => 'active',
            'expires_at' => now()->addMinutes(15),
        ]);

        $holdItem = InventoryHoldItem::factory()->create([
            'inventory_hold_id' => $hold->id,
            'quantity' => 2,
        ]);

        // Set held units
        $holdItem->inventory->update(['held_units' => 2]);

        $hold->release();

        $this->assertEquals(0, $holdItem->fresh()->inventory->held_units);
        $this->assertEquals(InventoryHold::STATUS_CANCELLED, $hold->fresh()->status);
    }
}
