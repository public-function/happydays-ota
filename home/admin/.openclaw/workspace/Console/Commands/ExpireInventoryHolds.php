<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\InventoryHoldService;

class ExpireInventoryHolds extends Command
{
    protected $signature = 'inventory:expire-holds';
    protected $description = 'Expire old inventory holds';
    
    public function handle(InventoryHoldService $holdService)
    {
        $expiredHolds = \App\Models\InventoryHold::where('status', 'active')
            ->where('expires_at', '<', now())
            ->get();
        
        foreach ($expiredHolds as $hold) {
            $holdService->expireHold($hold);
            $this->info("Expired hold: {$hold->hold_token}");
        }
        
        $this->info("Expired {$expiredHolds->count()} holds");
    }
}
