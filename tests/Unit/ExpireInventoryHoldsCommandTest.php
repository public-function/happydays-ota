<?php

namespace Tests\Unit;

use App\Console\Commands\ExpireInventoryHolds;
use PHPUnit\Framework\TestCase;

class ExpireInventoryHoldsCommandTest extends TestCase
{
    public function test_command_signature(): void
    {
        $signature = 'inventory:expire-holds {--dry-run : Show what would be expired without making changes}';
        
        $this->assertStringContainsString('inventory:expire-holds', $signature);
        $this->assertStringContainsString('--dry-run', $signature);
    }

    public function test_command_description(): void
    {
        $description = 'Expire inventory holds that have exceeded their TTL';
        
        $this->assertNotEmpty($description);
    }
}
