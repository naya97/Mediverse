<?php

use App\Http\Controllers\Doctor\DoctorProfileController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware([JwtMiddleware::class])->group(function () {
    Route::controller(DoctorProfileController::class)->group(function () {
        Route::get('profile', 'profile');
        Route::get('availableWorkDays', 'availableWorkDays');
        Route::get('showDoctorWorkDates', 'showDoctorWorkDates');
        Route::post('schedule', 'schedule');
        Route::post('deleteFromSchedule', 'deleteFromSchedule');
        Route::post('editProfile', 'editProfile');
        Route::get('showDoctorReviews', 'showDoctorReviews');
    });
});
