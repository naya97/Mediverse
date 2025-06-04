<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware([JwtMiddleware::class])->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
});

Route::post('/auth/google', [GoogleAuthController::class, 'googleLogin']);


Route::prefix('admin')->middleware(JwtMiddleware::class)->group(function () {
    require __DIR__.'/Admin/clinic.php';
    require __DIR__.'/Admin/dashboard.php';
    require __DIR__.'/Admin/doctor.php';
    require __DIR__.'/Admin/employee.php';
    require __DIR__.'/Admin/pharmacies.php';
});

Route::prefix('secretary')->middleware(JwtMiddleware::class)->group(function () {
    require __DIR__.'/Secretary/appointment.php';
    require __DIR__.'/Secretary/payment.php';
});