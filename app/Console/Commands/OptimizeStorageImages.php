<?php

namespace App\Console\Commands;

use App\Models\Drink;
use App\Models\MenuItem;
use App\Support\StorageImageOptimizer;
use Illuminate\Console\Command;

class OptimizeStorageImages extends Command
{
    protected $signature = 'storage:optimize-images {--force : Regenerate variants even if they already exist}';

    protected $description = 'Generate responsive image variants for menu items and drinks in storage';

    public function handle(): int
    {
        if (! extension_loaded('gd')) {
            $this->error('PHP GD extension is required to optimize storage images.');

            return self::FAILURE;
        }

        $paths = MenuItem::query()
            ->whereNotNull('image')
            ->pluck('image')
            ->merge(
                Drink::query()
                    ->whereNotNull('image')
                    ->pluck('image'),
            )
            ->filter()
            ->unique()
            ->values();

        if ($paths->isEmpty()) {
            $this->info('No storage images found to optimize.');

            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($paths->count());
        $bar->start();

        foreach ($paths as $path) {
            if ($this->option('force')) {
                StorageImageOptimizer::deleteVariants($path);
            }

            StorageImageOptimizer::optimize($path);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Optimized {$paths->count()} storage image(s).");

        return self::SUCCESS;
    }
}
