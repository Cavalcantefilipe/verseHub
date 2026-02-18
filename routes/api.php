<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BibleVersionController;
use App\Http\Controllers\VerseController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\VerseClassificationController;
use App\Http\Controllers\BibleController;

// ============================================
// Authentication Routes
// ============================================
Route::prefix('auth')->group(function () {
    // Public routes - Traditional authentication
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    // Public routes - Google OAuth
    Route::get('google/redirect', [AuthController::class, 'redirectToGoogle']);
    Route::get('google/callback', [AuthController::class, 'handleGoogleCallback']);
    Route::post('google/token', [AuthController::class, 'loginWithGoogleToken']);
    Route::post('google/code', [AuthController::class, 'loginWithGoogleCode']);
    Route::post('google/mobile-login', [AuthController::class, 'loginWithGoogleMobile']);

    // Protected routes (JWT)
    Route::middleware('auth:api')->group(function () {
        Route::get('user', [AuthController::class, 'user']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });
});

// Legacy user endpoint (kept for backwards compatibility)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

// Bible Versions (public read, auth required for write)
Route::get('bible-versions', [BibleVersionController::class, 'index']);
Route::get('bible-versions/{bible_version}', [BibleVersionController::class, 'show']);
Route::middleware('auth:api')->group(function () {
    Route::post('bible-versions', [BibleVersionController::class, 'store']);
    Route::put('bible-versions/{bible_version}', [BibleVersionController::class, 'update']);
    Route::patch('bible-versions/{bible_version}', [BibleVersionController::class, 'update']);
    Route::delete('bible-versions/{bible_version}', [BibleVersionController::class, 'destroy']);
});

// Verses (public read, auth required for creating new verses)
Route::get('verses', [VerseController::class, 'index']);
Route::get('verses/{verse}', [VerseController::class, 'show']);
Route::get('verses/search', [VerseController::class, 'search'])->name('verses.search');
Route::get('categories/{category}/verses', [VerseController::class, 'byCategory'])->name('verses.by-category');
Route::middleware('auth:api')->group(function () {
    Route::post('verses', [VerseController::class, 'store']); // Auth required for creating new verses
    Route::put('verses/{verse}', [VerseController::class, 'update']);
    Route::patch('verses/{verse}', [VerseController::class, 'update']);
    Route::delete('verses/{verse}', [VerseController::class, 'destroy']);
});

// Categories (public read, auth required for write)
Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{category}', [CategoryController::class, 'show']);
Route::middleware('auth:api')->group(function () {
    Route::post('categories', [CategoryController::class, 'store']);
    Route::put('categories/{category}', [CategoryController::class, 'update']);
    Route::patch('categories/{category}', [CategoryController::class, 'update']);
    Route::delete('categories/{category}', [CategoryController::class, 'destroy']);
});

// Verse Classifications (all require auth)
Route::middleware('auth:api')->group(function () {
    Route::apiResource('verse-classifications', VerseClassificationController::class);
    Route::post('verses/{verse}/classify', [VerseClassificationController::class, 'store'])->name('verses.classify');
    Route::delete('verses/{verse}/classifications/{category}', [VerseClassificationController::class, 'destroy'])->name('verses.unclassify');
    Route::post('check-classification', [VerseClassificationController::class, 'checkClassification'])->name('classifications.check');
});

// Public verse classification (legacy — kept for backwards compatibility, redirects to auth)
// Route::post('classify', ...); // REMOVED — use classify-auth instead
// Route::get('my-classifications', ...); // REMOVED — use my-classifications-auth instead

// Authenticated classification (requires Google login)
Route::middleware('auth:api')->group(function () {
    Route::post('classify', [VerseClassificationController::class, 'classifyAuth'])->name('classifications.public'); // legacy alias
    Route::post('classify-auth', [VerseClassificationController::class, 'classifyAuth'])->name('classifications.auth');
    Route::get('my-classifications', [VerseClassificationController::class, 'getByUser'])->name('classifications.by-device'); // legacy alias
    Route::get('my-classifications-auth', [VerseClassificationController::class, 'getByUser'])->name('classifications.by-user');
    Route::get('my-classification', [VerseClassificationController::class, 'getByReference'])->name('classifications.by-reference');
});
Route::get('verse-stats', [VerseClassificationController::class, 'getVerseStats'])->name('classifications.verse-stats');
Route::get('community-feed', [VerseClassificationController::class, 'communityFeed'])->name('classifications.community-feed');

// Public verse classifications view
Route::get('verses/{verse}/classifications', [VerseClassificationController::class, 'show'])->name('verses.classifications');

// Public stats endpoints
Route::get('popular-verses', function () {
    return app(\App\Services\VerseService::class)->getPopularVerses();
})->name('popular-verses');

Route::get('popular-categories', function () {
    return app(\App\Services\CategoryService::class)->getPopularCategories();
})->name('popular-categories');

Route::get('categories/{category}/top-verses', function (\App\Models\Category $category) {
    return app(\App\Services\VerseClassificationService::class)->getTopVersesForCategory($category);
})->name('categories.top-verses');

// Random verse endpoint
Route::get('verses/random', function (Illuminate\Http\Request $request) {
    $version = $request->get('version', 'nvi');
    $bibleService = app(\App\Services\BibleApiService::class);
    $randomVerse = $bibleService->getRandomVerse($version);

    if (!$randomVerse) {
        return response()->json([
            'message' => 'Unable to fetch random verse'
        ], 500);
    }

    return response()->json([
        'data' => $randomVerse,
        'message' => 'Random verse retrieved successfully'
    ]);
})->name('verses.random');

// Test Bible API connection
Route::get('bible-api/test', function () {
    $bibleService = app(\App\Services\BibleApiService::class);
    return response()->json($bibleService->testConnection());
})->name('bible-api.test');

// ============================================
// NEW: Bible Reading API - Fast & Scalable
// ============================================
Route::prefix('bible')->group(function () {
    // Get available Bible versions
    Route::get('versions', [BibleController::class, 'getVersions']);

    // Random verse from a version
    Route::get('{version}/random', [BibleController::class, 'getRandom']);

    // Search verses within a specific location
    Route::get('{version}/search', [BibleController::class, 'search']);

    // Book information
    Route::get('{version}/{book}/info', [BibleController::class, 'getBookInfo']);

    // Get all verses from a chapter
    Route::get('{version}/{book}/{chapter}', [BibleController::class, 'getChapter'])
        ->where(['chapter' => '[0-9]+']);

    // Get specific verse(s) - supports ranges (e.g., 3-10) or single verse (e.g., 16)
    Route::get('{version}/{book}/{chapter}/{verses}', [BibleController::class, 'getVerses'])
        ->where(['chapter' => '[0-9]+', 'verses' => '[0-9\-]+']);
});
