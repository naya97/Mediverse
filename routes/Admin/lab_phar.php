<?php

use App\Http\Controllers\Admin\Lab_PharmacyController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware([JwtMiddleware::class])->group(function () {
    Route::controller(Lab_PharmacyController::class)->group(function () {
        Route::post('add_Lab_Pharmacy', 'add');
        Route::post('update_Lab_Pharmacy', 'update');
        Route::post('delete_Lab_Pharmacy', 'delete');
    });
});
