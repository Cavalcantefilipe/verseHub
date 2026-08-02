<?php

namespace App\Http\Controllers;

use App\Models\DailyVerse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $date = now()->toDateString();
        $data = Cache::remember("home:v4:{$date}", now()->endOfDay(), function () use ($date) {
            $scheduled = DailyVerse::query()
                ->whereDate('publish_date', $date)
                ->where('is_active', true)
                ->orderBy('position')
                ->get()
                ->map(fn (DailyVerse $verse) => [
                    'reference' => $verse->reference,
                    'text' => $verse->text,
                    'version' => strtoupper($verse->version),
                    'book_abbrev' => $verse->book_abbrev,
                    'book_name' => $verse->book_name,
                    'chapter' => $verse->chapter,
                    'verse_number' => $verse->verse_number,
                ])->values();

            // Destaques são sempre editoriais. O índice bíblico contém versos
            // válidos para leitura, mas alguns registros curtos perdem contexto
            // quando exibidos isoladamente. Por isso, nunca escolhemos um deles
            // automaticamente como “versículo do dia”.
            $dailyVerses = $scheduled;

            $moments = DB::table('categories as c')
                ->join('category_groups as cg', function ($join) {
                    $join->on('cg.id', '=', 'c.category_group_id')
                        ->where('cg.status', 'approved')
                        ->where('cg.classification_kind', 'emotion');
                })
                ->leftJoin('user_verse_categories as uvc', 'uvc.category_id', '=', 'c.id')
                ->where('c.status', 'approved')
                ->groupBy('c.id', 'c.name', 'c.icon', 'c.color', 'c.display_order')
                ->orderByRaw('COUNT(uvc.id) DESC, c.display_order ASC')
                ->limit(8)
                ->get(['c.id', 'c.name', 'c.icon', 'c.color'])
                ->values();

            return [
                'date' => $date,
                'daily_verse' => $dailyVerses->first(),
                'daily_verses' => $dailyVerses,
                'moments' => $moments,
            ];
        });

        return response()->json(['data' => $data]);
    }
}
