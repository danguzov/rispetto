<?php

namespace App\Console\Commands;

use App\Services\Ordering\OrderAutoStatusService;
use Illuminate\Console\Command;

class ProcessOrderAutoStatusCommand extends Command
{
    protected $signature = 'orders:process-auto-status';

    protected $description = 'Automatski prebacuje porudžbine u pripremi/spremno na osnovu ETA';

    public function handle(OrderAutoStatusService $autoStatusService): int
    {
        $processed = $autoStatusService->processDueTransitions();

        if ($processed > 0) {
            $this->info("Ažurirano {$processed} prelaza statusa.");
        }

        return self::SUCCESS;
    }
}
