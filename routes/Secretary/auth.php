<?php

use App\Http\Controllers\Secretary\SecretaryAuthController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware([JwtMiddleware::class])->group(function () {
    Route::controller(SecretaryAuthController::class)->group(function () {
        Route::post('secretaryLogout',  'secretaryLogout');
        Route::post('secretarySaveFcmToken',  'secretarySaveFcmToken');
    });
});

Route::controller(SecretaryAuthController::class)->group(function () {
    Route::post('secretaryLogin',  'secretaryLogin');
});
