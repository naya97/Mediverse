<?php

use App\Http\Controllers\Patient\PatientController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;



Route::middleware([JwtMiddleware::class])->group(function () {
    Route::controller(PatientController::class)->group(function () {
        Route::post('/completeInfo','completePatientInfo');
        Route::post('/editProfile','editProfile');
    });
});