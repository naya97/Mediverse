<?php

use App\Http\Controllers\Secretary\AppointmentController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware([JwtMiddleware::class])->group(function () {
    Route::controller(AppointmentController::class)->group(function () {
        Route::get('/showAllAppointments', 'showAllAppointments');
        Route::post('/filteringAppointmentByDoctor', 'filteringAppointmentByDoctor');
        Route::post('/filteringAppointmentByMonth', 'filteringAppointmentByMonth');
        Route::post('/filteringAppointmentByDate', 'filteringAppointmentByDate');
        Route::post('/filteringAppointmentByStatus', 'filteringAppointmentByStatus');
        Route::post('/editSchedule', 'editSchedule');
        Route::get('/cancelAppointment', 'cancelAppointment');
        Route::get('/showAppointmentDetails', 'showAppointmentDetails');
        Route::get('/showTodayAppointmentByDoctor', 'showTodayAppointmentByDoctor');
    });
});
