<?php

namespace App\Console\Commands;

use App\Services\BibleApiService;
use Illuminate\Console\Command;

class TestBibleApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bible:test {--bible-version=nvi : Bible version to test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test connection to A Bíblia Digital API';

    /**
     * Execute the console command.
     */
    public function handle(BibleApiService $bibleApiService): int
    {
        $this->info('Testing A Bíblia Digital API connection...');
        $this->newLine();

        // Test authentication
        $this->info('1. Testing authentication...');
        $result = $bibleApiService->testConnection();

        if ($result['success']) {
            $this->info('✓ Authentication successful');
            $this->info('   Token: ' . $result['token']);
        } else {
            $this->error('✗ Authentication failed: ' . $result['message']);
            return Command::FAILURE;
        }

        $this->newLine();

        // Test random verse
        $this->info('2. Testing random verse endpoint...');
        $version = $this->option('bible-version');
        $randomVerse = $bibleApiService->getRandomVerse($version);

        if ($randomVerse) {
            $this->info('✓ Random verse retrieved');
            $this->info('   Book: ' . ($randomVerse['book']['name'] ?? 'N/A'));
            $this->info('   Chapter: ' . ($randomVerse['chapter'] ?? 'N/A'));
            $this->info('   Verse: ' . ($randomVerse['number'] ?? 'N/A'));
            $this->info('   Text: ' . substr($randomVerse['text'] ?? 'N/A', 0, 100) . '...');
        } else {
            $this->error('✗ Failed to retrieve random verse');
            return Command::FAILURE;
        }

        $this->newLine();

        // Test specific chapter
        $this->info('3. Testing specific chapter endpoint (João 3)...');
        $chapterData = $bibleApiService->getChapterVerses($version, 'jo', 3);

        if ($chapterData) {
            $verseCount = count($chapterData['verses'] ?? []);
            $this->info('✓ Chapter retrieved');
            $this->info('   Book: ' . ($chapterData['book']['name'] ?? 'N/A'));
            $this->info('   Chapter: ' . ($chapterData['chapter']['number'] ?? 'N/A'));
            $this->info('   Verses: ' . $verseCount);
        } else {
            $this->error('✗ Failed to retrieve chapter');
            return Command::FAILURE;
        }

        $this->newLine();

        // Test specific verse
        $this->info('4. Testing specific verse (João 3:16)...');
        $verseData = $bibleApiService->getVerse($version, 'jo', 3, 16);

        if ($verseData && isset($verseData['verses'][0])) {
            $verse = $verseData['verses'][0];
            $this->info('✓ Specific verse retrieved');
            $this->info('   Verse: ' . ($verse['number'] ?? 'N/A'));
            $this->info('   Text: ' . ($verse['text'] ?? 'N/A'));
        } else {
            $this->error('✗ Failed to retrieve specific verse');
            return Command::FAILURE;
        }

        $this->newLine();
        $this->info('✓ All tests passed!');
        $this->info('API is working correctly.');

        return Command::SUCCESS;
    }
}
