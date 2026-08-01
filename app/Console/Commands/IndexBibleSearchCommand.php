<?php

namespace App\Console\Commands;

use App\Models\BiblePassage;
use App\Services\BibleApiService;
use Illuminate\Console\Command;

class IndexBibleSearchCommand extends Command
{
    protected $signature = 'bible:index-search {--bible-version=* : Versões a indexar}';

    protected $description = 'Indexa os versículos em lotes para busca global local e rápida';

    private const BOOKS = [
        'gn' => 50, 'ex' => 40, 'lv' => 27, 'nm' => 36, 'dt' => 34, 'js' => 24, 'jz' => 21, 'rt' => 4, '1sm' => 31, '2sm' => 24,
        '1rs' => 22, '2rs' => 25, '1cr' => 29, '2cr' => 36, 'ed' => 10, 'ne' => 13, 'et' => 10, 'job' => 42, 'sl' => 150, 'pv' => 31,
        'ec' => 12, 'ct' => 8, 'is' => 66, 'jr' => 52, 'lm' => 5, 'ez' => 48, 'dn' => 12, 'os' => 14, 'jl' => 3, 'am' => 9, 'ob' => 1,
        'jn' => 4, 'mq' => 7, 'na' => 3, 'hc' => 3, 'sf' => 3, 'ag' => 2, 'zc' => 14, 'ml' => 4, 'mt' => 28, 'mc' => 16, 'lc' => 24,
        'jo' => 21, 'at' => 28, 'rm' => 16, '1co' => 16, '2co' => 13, 'gl' => 6, 'ef' => 6, 'fp' => 4, 'cl' => 4, '1ts' => 5, '2ts' => 3,
        '1tm' => 6, '2tm' => 4, 'tt' => 3, 'fm' => 1, 'hb' => 13, 'tg' => 5, '1pe' => 5, '2pe' => 3, '1jo' => 5, '2jo' => 1, '3jo' => 1,
        'jd' => 1, 'ap' => 22,
    ];

    public function handle(BibleApiService $bibleApi): int
    {
        $versions = $this->option('bible-version') ?: ['nvi', 'acf', 'ra'];
        $errors = 0;
        foreach ($versions as $version) {
            foreach (self::BOOKS as $book => $chapters) {
                for ($chapter = 1; $chapter <= $chapters; $chapter++) {
                    $data = $bibleApi->getChapterVerses($version, $book, $chapter);
                    if (! $data || empty($data['verses'])) {
                        $errors++;
                        $this->warn("Falha em {$version}/{$book}/{$chapter}");

                        continue;
                    }
                    $bookName = $data['book']['name'] ?? strtoupper($book);
                    $now = now();
                    $rows = collect($data['verses'])->map(fn ($verse) => [
                        'version' => strtolower($version), 'book_abbrev' => strtolower($book), 'book_name' => $bookName,
                        'chapter' => $chapter, 'verse_number' => (int) $verse['number'], 'text' => $verse['text'],
                        'created_at' => $now, 'updated_at' => $now,
                    ])->all();
                    BiblePassage::upsert($rows, ['version', 'book_abbrev', 'chapter', 'verse_number'], ['book_name', 'text', 'updated_at']);
                }
            }
            $this->info(strtoupper($version).' indexada.');
        }

        return $errors === 0 ? self::SUCCESS : self::FAILURE;
    }
}
