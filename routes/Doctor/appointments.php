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
        Route::get('showpatientAppointment', 'showpatientAppointment');
        Route::get('showAppointmentDetails', 'showAppointmentDetails');
        Route::get('showAppointmantResults', 'showAppointmantResults');
        Route::get('showDoctorWorkDays', 'showDoctorWorkDays');
        Route::get('showTimes', 'showTimes');
        Route::post('addCheckup', 'addCheckup');
    });
});
