<?php

use App\Http\Controllers\PharmacyController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware([JwtMiddleware::class])->group(function () {
    Route::controller(PharmacyController::class)->group(function () {
        Route::post('show', 'show');
        Route::post('search', 'search');
    });
});
