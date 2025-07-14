<?php

use App\Http\Controllers\Patient\Reservation\ReservationController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;



Route::middleware([JwtMiddleware::class])->group(function () {
    Route::controller(ReservationController::class)->group(function () {
        Route::get('/showDoctorWorkDays', 'showDoctorWorkDays');
        Route::post('/showTimes', 'showTimes');
        Route::post('/addReservation', 'addReservation');
        Route::post('/editReservation', 'editReservation');
        Route::get('/cancelReservation', 'cancelReservation');
    });
});
