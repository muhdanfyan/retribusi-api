<?php

use App\Http\Controllers\DocumentationController;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('docs')->group(function () {
    Route::get('/', [DocumentationController::class, 'index']);
    Route::get('/assets/{filename}', [DocumentationController::class, 'asset']);
    Route::get('/{page}', [DocumentationController::class, 'show']);
});
