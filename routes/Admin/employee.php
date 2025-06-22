<?php

use App\Http\Controllers\Admin\LabtechSecretaryController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware([JwtMiddleware::class])->group(function () {
    Route::controller(LabtechSecretaryController::class)->group(function () {
        Route::get('/showEmployee','showEmployee');
        Route::get('/showEmployeeByID','showEmployeeByID');
        Route::post('/addEmployee','addEmployee');
        Route::post('/editEmployee','editEmployee');
        Route::delete('/removeEmployee','removeEmployee');
    });
});