<?php

use App\Http\Controllers\Doctor\DoctorAuthController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware([JwtMiddleware::class])->group(function () {
    Route::controller(DoctorAuthController::class)->group(function () {
        Route::post('doctorLogout',  'doctorLogout');
        Route::post('doctorSaveFcmToken',  'doctorSaveFcmToken');
    });
});

Route::controller(DoctorAuthController::class)->group(function () {
    Route::post('doctorLogin',  'doctorLogin');
});
