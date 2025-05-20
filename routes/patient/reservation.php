<?php

use App\Http\Controllers\Patient\Reservation\ReservationController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;



Route::middleware([JwtMiddleware::class])->group(function () {
    Route::controller(ReservationController::class)->group(function () {
        Route::post('/showDoctorWorkDays','showDoctorWorkDays');
        Route::post('/showTimes','showTimes');
        Route::post('/addReservation','addReservation');
        Route::post('/editReservation','editReservation');
        Route::post('/cancelReservation','cancelReservation');
    });
});
