<?php

use App\Http\Controllers\Home\HomeController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;



Route::middleware([JwtMiddleware::class])->group(function () {
    Route::controller(HomeController::class)->group(function () {
        Route::get('/showDoctors', 'showDoctors');
        Route::post('/showDoctorDetails', 'showDoctorDetails');
        Route::post('/showClinincDoctors', 'showClinincDoctors');
        Route::post('/searchDoctor', 'searchDoctor');
        Route::get('/showClinics', 'showClinics');
    });
});
