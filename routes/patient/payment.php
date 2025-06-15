<?php

use App\Http\Controllers\Patient\PaymentController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;



Route::middleware([JwtMiddleware::class])->group(function () {
    Route::controller(PaymentController::class)->group(function () {
        Route::post('/createPaymentIntent','createPaymentIntent');
        Route::post('/confirmWalletRecharge','confirmWalletRecharge');
        Route::post('/createReservationPaymentIntent','createReservationPaymentIntent');
        Route::post('/confirmReservationPayment','confirmReservationPayment');
        Route::post('/cancelReservationAndRefund','cancelReservationAndRefund');
        Route::get('/showWalletRange','showWalletRange');
    });
});
