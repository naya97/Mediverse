<?php

use App\Http\Controllers\Admin\DashBoardController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware([JwtMiddleware::class])->group(function () {
    Route::controller(DashBoardController::class)->group(function () {
        Route::get('/showAllAppointments','showAllAppointments');
        Route::post('/filteringAppointmentByDoctor','filteringAppointmentByDoctor');
        Route::post('/filteringAppointmentByStatus','filteringAppointmentByStatus');
    });
});