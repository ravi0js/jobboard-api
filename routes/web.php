<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/docs', function () {
    $format = request()->query('api-docs.json') !== null ? 'json' : null;
    return response()->file(public_path('api-docs.json'), [
        'Content-Type' => 'application/json'
    ]);
});

Route::get('/api/documentation', function () {
    return response()->file(public_path('swagger-ui/index.html'));
});