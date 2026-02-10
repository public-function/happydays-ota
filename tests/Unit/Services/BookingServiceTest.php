<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\BookingItem;
use App\Models\HotelRoomTypeInventory;
use App\Models\ProductOffer;
use App\Models\HotelRoomType;
use App\Services\BookingService;
use App\Services\InventoryHoldService;

class BookingServiceTest extends TestCase
{
    protected BookingService $service;
    protected InventoryHoldService $holdService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->holdService = new InventoryHoldService();
        $this->service = new BookingService($this->holdService);
    }

    public function test_cancel_booking_item_restores_inventory(): void
    {
        // Arrange
        $offer = ProductOffer::factory()->create([
            'duration_nights' => 2,
        ]);

        $hotelRoomType = HotelRoomType::factory()->create();
        $hotelRoomType->productOffers()->attach($offer->id);

        $checkIn = now()->format('Y-m-d');

        // Create inventory with low availability
        for ($i = 0; $i < 2; $i++) {
            HotelRoomTypeInventory::factory()->create([
                'hotel_room_type_id' => $hotelRoomType->id,
                'night_date' => now()->addDays($i)->format('Y-m-d'),
                'available_units' => 5,
                'held_units' => 0,
            ]);
        }

        $booking = \App\Models\Booking::factory()->create(['status' => 'confirmed']);
        $bookingItem = BookingItem::factory()->create([
            'booking_id' => $booking->id,
            'product_offer_id' => $offer->id,
            'hotel_room_type_id' => $hotelRoomType->id,
            'check_in_date' => $checkIn,
            'nights' => 2,
            'quantity' => 2,
            'status' => 'confirmed',
        ]);

        $inventoryBefore = HotelRoomTypeInventory::all();
        $this->assertEquals(5, $inventoryBefore[0]->available_units);

        // Act
        $result = $this->service->cancelBookingItem($bookingItem->id, 'Customer requested cancellation');

        // Assert
        $this->assertEquals('cancelled', $result->status);
        $this->assertEquals('Customer requested cancellation', $result->notes);

        // Check inventory was restored
        $inventoryAfter = HotelRoomTypeInventory::all();
        $this->assertEquals(7, $inventoryAfter[0]->available_units); // 5 + 2 restored
    }

    public function test_generate_booking_reference_format(): void
    {
        // Arrange
        $offer = ProductOffer::factory()->create(['duration_nights' => 1]);
        $hotelRoomType = HotelRoomType::factory()->create();

        HotelRoomTypeInventory::factory()->create([
            'hotel_room_type_id' => $hotelRoomType->id,
            'night_date' => now()->format('Y-m-d'),
            'available_units' => 10,
            'held_units' => 0,
        ]);

        $hold = $this->holdService->createHold([
            'product_offer_id' => $offer->id,
            'hotel_room_type_id' => $hotelRoomType->id,
            'check_in_date' => now()->format('Y-m-d'),
            'quantity' => 1,
        ]);

        // Act
        $booking = $this->service->createBookingFromHold(
            $hold->hold_token,
            [
                'customer_name' => 'Test Customer',
                'customer_email' => 'test@example.com',
                'total_amount' => 500.00,
            ],
            [
                'product_offer_id' => $offer->id,
                'hotel_room_type_id' => $hotelRoomType->id,
                'check_in_date' => now()->format('Y-m-d'),
                'nights' => 1,
                'adults' => 2,
                'quantity' => 1,
                'unit_price' => 500.00,
            ],
            500.00
        );

        // Assert
        $this->assertMatchesRegularExpression('/^BK-\d{4}-\d{6}$/', $booking->booking_reference);
        $this->assertStringStartsWith('BK-' . date('Y') . '-', $booking->booking_reference);
    }
}
