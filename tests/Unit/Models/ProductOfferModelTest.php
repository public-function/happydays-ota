<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\ProductOffer;
use App\Models\Hotel;
use App\Models\RatePlan;
use App\Models\Inventory;
use App\Models\BookingItem;
use App\Models\HotelRoomType;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductOfferModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_offer_belongs_to_hotel(): void
    {
        $hotel = Hotel::factory()->create();
        $ratePlan = RatePlan::factory()->create();
        
        $productOffer = ProductOffer::factory()->create([
            'hotel_id' => $hotel->id,
            'rate_plan_id' => $ratePlan->id,
        ]);

        $this->assertInstanceOf(Hotel::class, $productOffer->hotel);
        $this->assertEquals($hotel->id, $productOffer->hotel->id);
    }

    public function test_product_offer_belongs_to_rate_plan(): void
    {
        $ratePlan = RatePlan::factory()->create();
        
        $productOffer = ProductOffer::factory()->create([
            'rate_plan_id' => $ratePlan->id,
        ]);

        $this->assertInstanceOf(RatePlan::class, $productOffer->ratePlan);
        $this->assertEquals($ratePlan->id, $productOffer->ratePlan->id);
    }

    public function test_product_offer_has_many_inventories(): void
    {
        $hotel = Hotel::factory()->create();
        $ratePlan = RatePlan::factory()->create();
        $hotelRoomType = HotelRoomType::factory()->create(['hotel_id' => $hotel->id]);
        
        $productOffer = ProductOffer::factory()->create([
            'hotel_id' => $hotel->id,
            'rate_plan_id' => $ratePlan->id,
        ]);

        Inventory::factory()->count(5)->create([
            'product_offer_id' => $productOffer->id,
            'hotel_room_type_id' => $hotelRoomType->id,
        ]);

        $this->assertCount(5, $productOffer->inventories);
    }

    public function test_product_offer_has_many_booking_items(): void
    {
        $hotel = Hotel::factory()->create();
        $ratePlan = RatePlan::factory()->create();
        $hotelRoomType = HotelRoomType::factory()->create(['hotel_id' => $hotel->id]);
        
        $productOffer = ProductOffer::factory()->create([
            'hotel_id' => $hotel->id,
            'rate_plan_id' => $ratePlan->id,
        ]);

        $booking = \App\Models\Booking::factory()->create();
        
        BookingItem::factory()->count(3)->create([
            'booking_id' => $booking->id,
            'product_offer_id' => $productOffer->id,
            'hotel_room_type_id' => $hotelRoomType->id,
        ]);

        $this->assertCount(3, $productOffer->bookingItems);
    }
}
