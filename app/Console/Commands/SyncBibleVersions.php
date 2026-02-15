<?php

namespace App\Console\Commands;

use App\Services\BibleApiService;
use Illuminate\Console\Command;

class SyncBibleVersions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bible:sync-versions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Bible versions from external API to database';

    /**
     * Execute the console command.
     */
    public function handle(BibleApiService $bibleApiService): int
    {
        $this->info('Syncing Bible versions from API...');

        try {
            $synced = $bibleApiService->syncBibleVersions();

            $this->info("Successfully synced {$synced} Bible versions.");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to sync Bible versions: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }
}
