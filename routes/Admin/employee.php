<?php

use App\Http\Controllers\Admin\LabtechSecretaryController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware([JwtMiddleware::class])->group(function () {
    Route::controller(LabtechSecretaryController::class)->group(function () {
        Route::post('/showEmployee','showEmployee');
        Route::post('/addEmployee','addEmployee');
        Route::post('/editEmployee','editEmployee');
        Route::post('/removeEmployee','removeEmployee');
    });
});