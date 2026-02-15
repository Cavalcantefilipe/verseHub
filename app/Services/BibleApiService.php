<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\BibleVersion;

class BibleApiService
{
    protected string $baseUrl;
    protected string $apiUser;
    protected string $apiPassword;
    protected ?string $token = null;

    public function __construct()
    {
        $this->baseUrl = config('services.bible_api.url', 'https://www.abibliadigital.com.br/api');
        $this->apiUser = config('services.bible_api.user');
        $this->apiPassword = config('services.bible_api.password');
    }

    /**
     * Get authentication token from API.
     */
    protected function getToken(): ?string
    {
        if ($this->token) {
            return $this->token;
        }

        $cacheKey = 'bible_api_token';

        return Cache::remember($cacheKey, now()->addHours(23), function () {
            try {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                ])->put("{$this->baseUrl}/users/token", [
                    'email' => $this->apiUser,
                    'password' => $this->apiPassword,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $this->token = $data['token'] ?? null;
                    return $this->token;
                }

                Log::error('Bible API Token Error: ' . $response->body());
            } catch (\Exception $e) {
                Log::error('Bible API Token Exception: ' . $e->getMessage());
            }

            return null;
        });
    }

    /**
     * Get chapter verses from API.
     * GET /verses/:version/:abbrev/:chapter
     */
    public function getChapterVerses(string $version, string $bookAbbrev, int $chapter): ?array
    {
        $cacheKey = "bible_chapter_{$version}_{$bookAbbrev}_{$chapter}";

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($version, $bookAbbrev, $chapter) {
            $token = $this->getToken();

            if (!$token) {
                Log::error('Bible API: Unable to get authentication token');
                return null;
            }

            try {
                $response = Http::withHeaders([
                    'Authorization' => "Bearer {$token}",
                ])->get("{$this->baseUrl}/verses/{$version}/{$bookAbbrev}/{$chapter}");

                if ($response->successful()) {
                    return $response->json();
                }

                Log::error('Bible API Chapter Error: ' . $response->body());
            } catch (\Exception $e) {
                Log::error('Bible API Chapter Exception: ' . $e->getMessage());
            }

            return null;
        });
    }

    /**
     * Get specific verse(s) from a chapter.
     */
    public function getVerse(string $version, string $bookAbbrev, int $chapter, int $verseStart, ?int $verseEnd = null): ?array
    {
        $chapterData = $this->getChapterVerses($version, $bookAbbrev, $chapter);

        if (!$chapterData || !isset($chapterData['verses'])) {
            return null;
        }

        $verses = collect($chapterData['verses'])
            ->filter(function ($verse) use ($verseStart, $verseEnd) {
                $verseNumber = $verse['number'] ?? 0;
                if ($verseEnd) {
                    return $verseNumber >= $verseStart && $verseNumber <= $verseEnd;
                }
                return $verseNumber == $verseStart;
            })
            ->values()
            ->toArray();

        return [
            'book' => $chapterData['book'] ?? null,
            'chapter' => $chapterData['chapter'] ?? null,
            'verses' => $verses,
        ];
    }

    /**
     * Get random verse.
     * GET /verses/:version/random
     */
    public function getRandomVerse(string $version = 'nvi'): ?array
    {
        $token = $this->getToken();

        if (!$token) {
            Log::error('Bible API: Unable to get authentication token');
            return null;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$token}",
            ])->get("{$this->baseUrl}/verses/{$version}/random");

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Bible API Random Verse Error: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Bible API Random Verse Exception: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Get available Bible versions from seeded data.
     */
    public function getAvailableVersions(): array
    {
        return BibleVersion::orderBy('language')
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    /**
     * Search for verses by book and chapter.
     */
    public function searchVerses(string $version, string $bookAbbrev, ?int $chapter = null): ?array
    {
        if ($chapter) {
            return $this->getChapterVerses($version, $bookAbbrev, $chapter);
        }

        // If no chapter specified, return book info
        // This would need to be expanded based on API capabilities
        return null;
    }

    /**
     * Clear authentication token cache.
     */
    public function clearTokenCache(): void
    {
        Cache::forget('bible_api_token');
        $this->token = null;
    }

    /**
     * Test API connection and authentication.
     */
    public function testConnection(): array
    {
        $token = $this->getToken();

        if (!$token) {
            return [
                'success' => false,
                'message' => 'Failed to authenticate with Bible API',
            ];
        }

        // Try to get a random verse to test the connection
        $randomVerse = $this->getRandomVerse('nvi');

        if ($randomVerse) {
            return [
                'success' => true,
                'message' => 'Successfully connected to Bible API',
                'token' => substr($token, 0, 10) . '...',
                'sample_verse' => $randomVerse,
            ];
        }

        return [
            'success' => false,
            'message' => 'Authenticated but failed to fetch data',
            'token' => substr($token, 0, 10) . '...',
        ];
    }
}
