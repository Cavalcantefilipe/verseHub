<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/admin/settings', function () {
    return view('admin.settings');
});
