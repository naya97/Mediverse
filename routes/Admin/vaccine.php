<?php

use App\Http\Controllers\Admin\VaccineController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware([JwtMiddleware::class])->group(function () {
    Route::controller(VaccineController::class)->group(function () {
        Route::get('/show','show');
        Route::get('/showDetails','showDetails');
        Route::post('/add','add');
        Route::post('/edit','edit');
        Route::delete('/remove','remove');
    });
});