<?php

use App\Http\Controllers\Secretary\PaymentController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware([JwtMiddleware::class])->group(function () {
    Route::controller(PaymentController::class)->group(function () {
        Route::post('addBill', 'addBill');
    });
});