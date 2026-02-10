<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Hotel;
use App\Models\RoomType;
use App\Models\HotelRoomType;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HotelModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_hotel_has_many_room_types(): void
    {
        $hotel = Hotel::factory()->create();
        $roomType1 = RoomType::factory()->create();
        $roomType2 = RoomType::factory()->create();

        HotelRoomType::factory()->create(['hotel_id' => $hotel->id]);
        HotelRoomType::factory()->create(['hotel_id' => $hotel->id]);

        $this->assertCount(2, $hotel->hotelRoomTypes);
        $this->assertInstanceOf(HotelRoomType::class, $hotel->hotelRoomTypes->first());
    }

    public function test_hotel_room_type_belongs_to_hotel(): void
    {
        $hotel = Hotel::factory()->create();
        $hotelRoomType = HotelRoomType::factory()->create(['hotel_id' => $hotel->id]);

        $this->assertInstanceOf(Hotel::class, $hotelRoomType->hotel);
        $this->assertEquals($hotel->id, $hotelRoomType->hotel->id);
    }

    public function test_hotel_has_many_product_offers(): void
    {
        $hotel = Hotel::factory()->create();
        $ratePlan = \App\Models\RatePlan::factory()->create();

        \App\Models\ProductOffer::factory()->count(3)->create([
            'hotel_id' => $hotel->id,
            'rate_plan_id' => $ratePlan->id,
        ]);

        $this->assertCount(3, $hotel->productOffers);
    }

    public function test_hotel_soft_deletes(): void
    {
        $hotel = Hotel::factory()->create();
        $hotelId = $hotel->id;

        $hotel->delete();

        $this->assertSoftDeleted('hotels', ['id' => $hotelId]);
        $this->assertNull(Hotel::find($hotelId));
        $this->assertNotNull(Hotel::withTrashed()->find($hotelId));
    }
}
