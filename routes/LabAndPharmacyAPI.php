<?php

use App\Http\Controllers\LabAndPharmacyController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware([JwtMiddleware::class])->group(function () {
    Route::controller(LabAndPharmacyController::class)->group(function () {
        Route::post('show', 'show');
        Route::post('search', 'search');
    });
});
