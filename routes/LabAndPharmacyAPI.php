<?php

use App\Http\Controllers\LabAndPharmacyController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware([JwtMiddleware::class])->group(function () {
    Route::post('add', [LabAndPharmacyController::class, 'add']);
    Route::post('show', [LabAndPharmacyController::class, 'show']);
});
