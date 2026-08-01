<?php

namespace App\Http\Controllers;

use App\Models\BiblePassage;
use App\Services\BibleApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function __invoke(BibleApiService $bibleApi): JsonResponse
    {
        $date = now()->toDateString();
        $data = Cache::remember("home:v1:{$date}", now()->endOfDay(), function () use ($bibleApi, $date) {
            $count = BiblePassage::where('version', 'nvi')->count();
            if ($count > 0) {
                $offset = abs(crc32($date)) % $count;
                $passage = BiblePassage::where('version', 'nvi')->orderBy('id')->offset($offset)->first();
                $daily = [
                    'reference' => "{$passage->book_name} {$passage->chapter}:{$passage->verse_number}",
                    'text' => $passage->text,
                    'version' => strtoupper($passage->version),
                    'book_abbrev' => $passage->book_abbrev,
                    'book_name' => $passage->book_name,
                    'chapter' => $passage->chapter,
                    'verse_number' => $passage->verse_number,
                ];
            } else {
                $daily = $bibleApi->getRandomVerse('nvi');
            }

            $moments = DB::table('categories as c')
                ->leftJoin('user_verse_categories as uvc', 'uvc.category_id', '=', 'c.id')
                ->where('c.status', 'approved')
                ->groupBy('c.id', 'c.name', 'c.icon', 'c.color')
                ->orderByRaw('COUNT(uvc.id) DESC')
                ->limit(8)
                ->get(['c.id', 'c.name', 'c.icon', 'c.color'])
                ->values();

            return ['date' => $date, 'daily_verse' => $daily, 'moments' => $moments];
        });

        return response()->json(['data' => $data]);
    }
}
