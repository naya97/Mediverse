<?php

use App\Http\Controllers\Admin\AdminAuthContcoller;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware([JwtMiddleware::class])->group(function () {
    Route::controller(AdminAuthContcoller::class)->group(function () {
        Route::post('adminLogout',  'adminLogout');
        Route::post('adminSaveFcmToken',  'adminSaveFcmToken');
    });
});

Route::controller(AdminAuthContcoller::class)->group(function () {
    Route::post('adminLogin',  'adminLogin');
});
