<?php

use App\Http\Controllers\Patient\AppointmentController;
use App\Http\Controllers\Patient\PatientController;
use App\Http\Controllers\Patient\Rate\RateController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;



Route::middleware([JwtMiddleware::class])->group(function () {
    Route::controller(PatientController::class)->group(function () {
        Route::post('/completeInfo','completePatientInfo');
        Route::post('/editProfile','editProfile');
        Route::get('/showProfile','showProfile');
    });
    Route::controller(RateController::class)->group(function () {
        Route::post('/rate', 'patientRate');
    });
    Route::controller(AppointmentController::class)->group(function () {
        Route::post('showAppointment', 'showAppointment');
    });
});
