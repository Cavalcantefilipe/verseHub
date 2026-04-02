<?php

namespace App\Console\Commands;

use App\Services\BibleApiService;
use Illuminate\Console\Command;

class WarmBibleCacheCommand extends Command
{
    protected $signature = 'bible:warm-cache {--bible-version=* : Versões para cachear (padrão: nvi, acf, ra)}';
    protected $description = 'Busca todos os capítulos da Bíblia na API externa e popula o cache';

    // Livros com quantidade de capítulos
    private const BOOKS = [
        'gn' => 50, 'ex' => 40, 'lv' => 27, 'nm' => 36, 'dt' => 34,
        'js' => 24, 'jz' => 21, 'rt' => 4, '1sm' => 31, '2sm' => 24,
        '1rs' => 22, '2rs' => 25, '1cr' => 29, '2cr' => 36, 'ed' => 10,
        'ne' => 13, 'et' => 10, 'job' => 42, 'sl' => 150, 'pv' => 31,
        'ec' => 12, 'ct' => 8, 'is' => 66, 'jr' => 52, 'lm' => 5,
        'ez' => 48, 'dn' => 12, 'os' => 14, 'jl' => 3, 'am' => 9,
        'ob' => 1, 'jn' => 4, 'mq' => 7, 'na' => 3, 'hc' => 3,
        'sf' => 3, 'ag' => 2, 'zc' => 14, 'ml' => 4,
        'mt' => 28, 'mc' => 16, 'lc' => 24, 'jo' => 21, 'at' => 28,
        'rm' => 16, '1co' => 16, '2co' => 13, 'gl' => 6, 'ef' => 6,
        'fp' => 4, 'cl' => 4, '1ts' => 5, '2ts' => 3, '1tm' => 6,
        '2tm' => 4, 'tt' => 3, 'fm' => 1, 'hb' => 13, 'tg' => 5,
        '1pe' => 5, '2pe' => 3, '1jo' => 5, '2jo' => 1, '3jo' => 1,
        'jd' => 1, 'ap' => 22,
    ];

    public function handle(BibleApiService $bibleApi): int
    {
        $versions = $this->option('bible-version');
        if (empty($versions)) {
            $versions = ['nvi', 'acf', 'ra'];
        }

        $totalChapters = array_sum(self::BOOKS);
        $totalRequests = $totalChapters * count($versions);
        $this->info("Cacheando {$totalRequests} capítulos (" . count($versions) . " versões)...");

        $bar = $this->output->createProgressBar($totalRequests);
        $bar->start();

        $cached = 0;
        $errors = 0;

        foreach ($versions as $version) {
            foreach (self::BOOKS as $book => $chapters) {
                for ($ch = 1; $ch <= $chapters; $ch++) {
                    try {
                        $bibleApi->getChapterVerses($version, $book, $ch);
                        $cached++;
                    } catch (\Exception $e) {
                        $errors++;
                        $this->newLine();
                        $this->warn("Erro: {$version}/{$book}/{$ch} — {$e->getMessage()}");
                    }

                    $bar->advance();

                    // Pausa para não sobrecarregar a API externa
                    usleep(200_000); // 200ms entre requests
                }
            }
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Concluído! {$cached} capítulos cacheados, {$errors} erros.");

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }
}
