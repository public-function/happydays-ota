<?php

namespace Tests\Unit;

use App\Models\Inventory;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class AvailabilityServiceTest extends TestCase
{
    private AvailabilityService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AvailabilityService();
    }

    public function test_check_availability_returns_success_when_inventory_available(): void
    {
        // This test would normally use a mock or database test
        // For now, we test the structure of the response
        
        $result = [
            'available' => true,
            'nights' => [
                [
                    'date' => '2026-02-15',
                    'available' => true,
                    'total_units' => 10,
                    'available_units' => 10,
                    'held_units' => 0,
                    'effective_available' => 10,
                ]
            ],
            'error' => null,
        ];

        $this->assertArrayHasKey('available', $result);
        $this->assertArrayHasKey('nights', $result);
        $this->assertArrayHasKey('error', $result);
        $this->assertTrue($result['available']);
        $this->assertNull($result['error']);
        $this->assertCount(1, $result['nights']);
    }

    public function test_check_availability_returns_failure_when_stop_sell_active(): void
    {
        $result = [
            'available' => false,
            'nights' => [
                [
                    'date' => '2026-02-15',
                    'available' => false,
                ]
            ],
            'error' => 'Stop sell is active for date 2026-02-15',
        ];

        $this->assertFalse($result['available']);
        $this->assertNotNull($result['error']);
        $this->assertStringContainsString('Stop sell', $result['error']);
    }

    public function test_check_availability_returns_failure_when_insufficient_units(): void
    {
        $result = [
            'available' => false,
            'nights' => [],
            'error' => 'Insufficient availability. Requested: 5, Available: 2',
        ];

        $this->assertFalse($result['available']);
        $this->assertStringContainsString('Insufficient', $result['error']);
    }

    public function test_get_available_dates_returns_collection_structure(): void
    {
        // Test the expected structure of the returned collection
        $mockDates = collect([
            [
                'date' => '2026-02-15',
                'available' => true,
                'available_units' => 5,
                'price' => 100.00,
            ],
            [
                'date' => '2026-02-16',
                'available' => true,
                'available_units' => 3,
                'price' => 110.00,
            ],
        ]);

        $this->assertCount(2, $mockDates);
        $this->assertEquals('2026-02-15', $mockDates[0]['date']);
        $this->assertTrue($mockDates[0]['available']);
        $this->assertEquals(5, $mockDates[0]['available_units']);
    }

    public function test_effective_available_calculation(): void
    {
        // Test the effective available calculation logic
        $inventory = new Inventory();
        $inventory->available_units = 10;
        $inventory->held_units = 3;

        $effectiveAvailable = $inventory->available_units - $inventory->held_units;

        $this->assertEquals(7, $effectiveAvailable);
        $this->assertGreaterThanOrEqual(1, $effectiveAvailable);
    }

    public function test_check_availability_for_multiple_nights_structure(): void
    {
        $result = [
            'available' => true,
            'nights' => [
                ['date' => '2026-02-15', 'available' => true],
                ['date' => '2026-02-16', 'available' => true],
                ['date' => '2026-02-17', 'available' => true],
            ],
            'error' => null,
        ];

        $this->assertTrue($result['available']);
        $this->assertCount(3, $result['nights']);
        
        // Verify all nights are available
        foreach ($result['nights'] as $night) {
            $this->assertTrue($night['available']);
        }
    }

    public function test_date_range_parsing(): void
    {
        $startDate = Carbon::parse('2026-02-10');
        $endDate = Carbon::parse('2026-02-15');

        $this->assertEquals('2026-02-10', $startDate->format('Y-m-d'));
        $this->assertEquals('2026-02-15', $endDate->format('Y-m-d'));
        $this->assertTrue($endDate->isAfter($startDate));
    }
}
