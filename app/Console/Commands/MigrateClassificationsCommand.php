<?php

namespace App\Console\Commands;

use App\Models\BibleVerse;
use App\Models\UserVerseCategory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateClassificationsCommand extends Command
{
    protected $signature = 'classifications:migrate {--dry-run : Show what would be migrated without making changes}';

    protected $description = 'Migrate data from public_classifications to the new scalable structure (bible_verses + user_verse_categories)';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 DRY RUN — no changes will be made.');
        }

        // Check if old table exists
        if (!DB::getSchemaBuilder()->hasTable('public_classifications')) {
            $this->error('Table public_classifications does not exist.');
            return 1;
        }

        $total = DB::table('public_classifications')->count();
        $this->info("Found {$total} records in public_classifications.");

        if ($total === 0) {
            $this->info('Nothing to migrate.');
            return 0;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $versesCreated = 0;
        $classificationsCreated = 0;
        $skipped = 0;
        $errors = 0;

        $verseCache = [];

        DB::table('public_classifications')->orderBy('id')->chunk(100, function ($classifications) use (
            &$verseCache,
            &$versesCreated,
            &$classificationsCreated,
            &$skipped,
            &$errors,
            $bar,
            $dryRun
        ) {
            foreach ($classifications as $record) {
                try {
                    $categoryIds = is_string($record->category_ids)
                        ? json_decode($record->category_ids, true)
                        : $record->category_ids;

                    if (empty($categoryIds) || !is_array($categoryIds)) {
                        $skipped++;
                        $bar->advance();
                        continue;
                    }

                    $cacheKey = "{$record->reference}|{$record->version}";

                    if ($dryRun) {
                        if (!isset($verseCache[$cacheKey])) {
                            $verseCache[$cacheKey] = true;
                            $versesCreated++;
                        }
                        $classificationsCreated += count($categoryIds);
                        $bar->advance();
                        continue;
                    }

                    // Find or create bible_verse
                    if (!isset($verseCache[$cacheKey])) {
                        $bibleVerse = BibleVerse::firstOrCreate(
                            [
                                'reference' => $record->reference,
                                'version' => $record->version,
                            ],
                            [
                                'text' => $record->text,
                                'created_at' => $record->created_at,
                                'updated_at' => $record->updated_at,
                            ]
                        );

                        if ($bibleVerse->wasRecentlyCreated) {
                            $versesCreated++;
                        }

                        $verseCache[$cacheKey] = $bibleVerse->id;
                    }

                    $bibleVerseId = $verseCache[$cacheKey];

                    // Create pivot entries
                    foreach ($categoryIds as $categoryId) {
                        $exists = DB::table('categories')->where('id', $categoryId)->exists();
                        if (!$exists) {
                            $skipped++;
                            continue;
                        }

                        $query = UserVerseCategory::query()
                            ->where('bible_verse_id', $bibleVerseId)
                            ->where('category_id', $categoryId);

                        if ($record->user_id) {
                            $query->where('user_id', $record->user_id);
                        } else {
                            $query->where('device_id', $record->device_id)->whereNull('user_id');
                        }

                        if (!$query->exists()) {
                            UserVerseCategory::create([
                                'user_id' => $record->user_id,
                                'device_id' => $record->device_id,
                                'bible_verse_id' => $bibleVerseId,
                                'category_id' => $categoryId,
                                'created_at' => $record->created_at,
                                'updated_at' => $record->updated_at,
                            ]);
                            $classificationsCreated++;
                        } else {
                            $skipped++;
                        }
                    }
                } catch (\Exception $e) {
                    $errors++;
                    $this->newLine();
                    $this->error("Error migrating record #{$record->id}: {$e->getMessage()}");
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);

        $prefix = $dryRun ? '[DRY RUN] Would have ' : '';
        $this->info("{$prefix}Created {$versesCreated} bible verses.");
        $this->info("{$prefix}Created {$classificationsCreated} classification entries.");
        $this->info("Skipped: {$skipped}");

        if ($errors > 0) {
            $this->warn("Errors: {$errors}");
        }

        $this->newLine();
        $this->info('✅ Migration complete!');

        return 0;
    }
}
