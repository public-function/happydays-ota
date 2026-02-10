<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\InventoryHoldService;

class ExpireInventoryHolds extends Command
{
    protected $signature = 'inventory:expire-holds 
                            {--dry-run : Show what would be expired without making changes}';

    protected $description = 'Expire old inventory holds and release their inventory';

    public function handle(InventoryHoldService $holdService)
    {
        $dryRun = $this->option('dry-run');

        $expiredHolds = InventoryHold::where('status', 'active')
            ->where('expires_at', '<', now())
            ->get();

        if ($expiredHolds->isEmpty()) {
            $this->info('No expired holds found.');
            return 0;
        }

        $this->info("Found {$expiredHolds->count()} expired hold(s).");

        if ($dryRun) {
            foreach ($expiredHolds as $hold) {
                $this->line("Would expire: {$hold->hold_token}");
            }
            return 0;
        }

        $bar = $this->output->createProgressBar($expiredHolds->count());

        foreach ($expiredHolds as $hold) {
            try {
                $holdService->expireHold($hold);
                $this->info("Expired hold: {$hold->hold_token}");
            } catch (\Exception $e) {
                $this->error("Failed to expire hold {$hold->hold_token}: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Successfully expired {$expiredHolds->count()} hold(s).");

        return 0;
    }
}
