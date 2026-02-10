<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class OtaConfigTest extends TestCase
{
    public function test_config_file_exists(): void
    {
        $configPath = base_path('config/ota.php');
        
        $this->assertFileExists($configPath);
    }

    public function test_hold_ttl_minutes_config(): void
    {
        $config = require base_path('config/ota.php');
        
        $this->assertArrayHasKey('hold_ttl_minutes', $config);
        $this->assertEquals(15, $config['hold_ttl_minutes']);
    }

    public function test_max_hold_quantity_config(): void
    {
        $config = require base_path('config/ota.php');
        
        $this->assertArrayHasKey('max_hold_quantity', $config);
        $this->assertIsInt($config['max_hold_quantity']);
    }

    public function test_auto_expire_holds_config(): void
    {
        $config = require base_path('config/ota.php');
        
        $this->assertArrayHasKey('auto_expire_holds', $config);
        $this->assertIsBool($config['auto_expire_holds']);
    }

    public function test_booking_config_section(): void
    {
        $config = require base_path('config/ota.php');
        
        $this->assertArrayHasKey('booking', $config);
        $this->assertIsArray($config['booking']);
        
        $booking = $config['booking'];
        $this->assertArrayHasKey('min_advance_days', $booking);
        $this->assertArrayHasKey('max_advance_days', $booking);
        $this->assertArrayHasKey('default_currency', $booking);
    }

    public function test_inventory_config_section(): void
    {
        $config = require base_path('config/ota.php');
        
        $this->assertArrayHasKey('inventory', $config);
        $this->assertIsArray($config['inventory']);
        
        $inventory = $config['inventory'];
        $this->assertArrayHasKey('default_stop_sell', $inventory);
        $this->assertArrayHasKey('buffer_units', $inventory);
    }
}
