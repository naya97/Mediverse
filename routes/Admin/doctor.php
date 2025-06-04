<?php

use App\Http\Controllers\Admin\DoctorController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware([JwtMiddleware::class])->group(function () {
    Route::controller(DoctorController::class)->group(function () {
        Route::get('/showDoctors','showDoctors');
        Route::post('/addDoctor','addDoctor');
        Route::delete('/removeDoctor','removeDoctor');
        Route::get('/showDoctorReviews','showDoctorReviews');
    });
});