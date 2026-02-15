<?php

namespace App\Jobs;

use App\Models\VerseStat;
use App\Models\VerseClassification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class UpdateVerseStats implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $verseId,
        public int $categoryId
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $count = VerseClassification::where('verse_id', $this->verseId)
            ->where('category_id', $this->categoryId)
            ->count();

        VerseStat::updateOrCreate([
            'verse_id' => $this->verseId,
            'category_id' => $this->categoryId,
        ], [
            'votes' => $count,
            'updated_at' => now(),
        ]);
    }
}
