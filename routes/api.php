<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\AdminAuthContcoller;

Route::get('/user', [AuthController::class, 'getUser']);

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware([JwtMiddleware::class])->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
});

Route::post('/auth/google', [GoogleAuthController::class, 'googleLogin']);


Route::prefix('admin')->middleware(JwtMiddleware::class)->group(function () {
    require __DIR__ . '/Admin/clinic.php';
    require __DIR__ . '/Admin/dashboard.php';
    require __DIR__ . '/Admin/doctor.php';
    require __DIR__ . '/Admin/employee.php';
    require __DIR__ . '/Admin/pharmacies.php';
});

Route::prefix('admin')->group(function() {
    require __DIR__ . '/Admin/auth.php';
});

Route::prefix('secretary')->middleware(JwtMiddleware::class)->group(function () {
    require __DIR__ . '/Secretary/appointment.php';
    require __DIR__ . '/Secretary/payment.php';
});

Route::prefix('doctor')->middleware(JwtMiddleware::class)->group(function () {
    require __DIR__ . '/Doctor/appointments.php';
    require __DIR__ . '/Doctor/patientInfo.php';
    require __DIR__ . '/Doctor/profile.php';
});

Route::prefix('home')->middleware(JwtMiddleware::class)->group(function () {
    require __DIR__ . '/Home/home.php';
});

Route::prefix('labtech')->middleware(JwtMiddleware::class)->group(function () {
    require __DIR__ . '/LabTech/analysis.php';
});

Route::prefix('patient')->middleware(JwtMiddleware::class)->group(function () {
    require __DIR__ . '/patient/analysis.php';
    require __DIR__ . '/patient/reservation.php';
    require __DIR__ . '/patient/api.php';
});
