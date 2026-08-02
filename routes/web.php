<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/v', function (Request $request) {
    $reference = mb_substr((string) $request->query('reference', 'Salmos 23:1'), 0, 255);
    $version = mb_substr((string) $request->query('version', 'NVI'), 0, 10);
    $appUrl = 'bibleversemobile://v?'.http_build_query([
        'reference' => $reference,
        'version' => $version,
    ]);

    return view('verse-deep-link', compact('reference', 'version', 'appUrl'));
})->name('verse.deep-link');

Route::get('/', function () {
    return view('welcome');
});

Route::get('/privacy', function () {
    return view('privacy');
});

Route::get('/terms', function () {
    return view('terms');
});

Route::get('/delete-account', function () {
    return view('delete-account');
});

Route::get('/support', function () {
    return view('support');
});

Route::get('/admin/settings', function () {
    return view('admin.settings');
});
