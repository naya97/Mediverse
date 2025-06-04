<?php

use App\Http\Controllers\Admin\DashBoardController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware([JwtMiddleware::class])->group(function () {
    Route::controller(DashBoardController::class)->group(function () {
        Route::get('/showAllAppointments','showAllAppointments');
        Route::get('/filteringAppointmentByDoctor','filteringAppointmentByDoctor');
        Route::post('/filteringAppointmentByStatus','filteringAppointmentByStatus');
        Route::get('/showPaymentDetails','showPaymentDetails');
        Route::get('/showPaymentDetailsByDoctor','showPaymentDetailsByDoctor');
        Route::post('/showPaymentDetailsByDate','showPaymentDetailsByDate');
    });
});