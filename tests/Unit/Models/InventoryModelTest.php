<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Inventory;
use App\Models\InventoryHold;
use App\Models\InventoryHoldItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class InventoryModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_belongs_to_product_offer(): void
    {
        $inventory = Inventory::factory()->create();

        $this->assertInstanceOf(\App\Models\ProductOffer::class, $inventory->productOffer);
    }

    public function test_inventory_belongs_to_hotel_room_type(): void
    {
        $inventory = Inventory::factory()->create();

        $this->assertInstanceOf(\App\Models\HotelRoomType::class, $inventory->hotelRoomType);
    }

    public function test_inventory_has_many_hold_items(): void
    {
        $inventory = Inventory::factory()->create();
        
        InventoryHoldItem::factory()->count(2)->create(['inventory_id' => $inventory->id]);

        $this->assertCount(2, $inventory->holdItems);
    }

    public function test_inventory_calculates_available_correctly(): void
    {
        $inventory = Inventory::factory()->create([
            'total_units' => 10,
            'held_units' => 3,
            'available_units' => 7,
        ]);

        $this->assertEquals(7, $inventory->available_units);
    }
}
