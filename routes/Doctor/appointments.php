<?php

use App\Http\Controllers\Doctor\AppointmentController;
use App\Http\Controllers\Doctor\PatientInfoController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware([JwtMiddleware::class])->group(function () {
    Route::controller(AppointmentController::class)->group(function () {
        Route::get('showAllAppointments', 'showAllAppointments');
        Route::post('showAppointmentsByStatus', 'showAppointmentsByStatus');
        Route::post('showAppointmentsByType', 'showAppointmentsByType');
        Route::post('showAppointmentDetails', 'showAppointmentDetails');
    });
});
