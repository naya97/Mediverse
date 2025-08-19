<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\AdminAuthContcoller;
use App\Http\Controllers\Chat\ChatController;
use App\Http\Controllers\Chat\ChatMessageController;
use App\Http\Controllers\Notifications\NotificationController;
use App\Services\FirebaseService;
use App\Http\Controllers\EmailOtpController;
use App\Http\Controllers\SmsOtpController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Broadcast;

Broadcast::routes(['middleware' => [JwtMiddleware::class]]);

Route::post('send_notification', [NotificationController::class, 'sendPushNotification']);

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware([JwtMiddleware::class])->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('/saveFcmToken', [AuthController::class, 'saveFcmToken']);
    Route::get('/getUser', [AuthController::class, 'getUser']);
});

Route::post('/auth/google', [GoogleAuthController::class, 'googleLogin']);

//Email Verification
Route::post('/send-email-otp', [EmailOtpController::class, 'send']);
Route::post('/verify-email-otp', [EmailOtpController::class, 'verify']);
Route::post('/email_resetPassword', [EmailOtpController::class, 'email_resetPassword']);

//SMS Verification
Route::post('/send-sms-otp', [SmsOtpController::class, 'send']);
Route::post('/verify-sms-otp', [SmsOtpController::class, 'verify']);
Route::post('/phone_resetPassword', [SmsOtpController::class, 'phone_resetPassword']);

//Notifications
Route::middleware([JwtMiddleware::class])->group(function () {
    Route::controller(NotificationController::class)->group(function () {
        Route::get('getAllNotifications', 'getAllNotifications');
        Route::get('getUnreadNotificationsCount', 'getUnreadNotificationsCount');
        Route::post('markNotificationAsRead', 'markNotificationAsRead');
    });
});

//Chat
Route::middleware([JwtMiddleware::class])->group(function () {
    Route::apiResource('chat', ChatController::class)->only(['index', 'store', 'show']);
    Route::apiResource('chat_message', ChatMessageController::class)->only(['index', 'store']);
    Route::apiResource('user', UserController::class)->only(['index']);
});


Route::prefix('admin')->middleware(JwtMiddleware::class)->group(function () {
    require __DIR__ . '/Admin/clinic.php';
    require __DIR__ . '/Admin/discount.php';
    require __DIR__ . '/Admin/dashboard.php';
    require __DIR__ . '/Admin/doctor.php';
    require __DIR__ . '/Admin/employee.php';
    require __DIR__ . '/Admin/pharmacies.php';
    require __DIR__ . '/Admin/vaccine.php';
});

Route::prefix('admin')->group(function () {
    require __DIR__ . '/Admin/auth.php';
});
Route::prefix('secretary')->group(function () {
    require __DIR__ . '/Secretary/auth.php';
});
Route::prefix('doctor')->group(function () {
    require __DIR__ . '/Doctor/auth.php';
});

Route::prefix('secretary')->middleware(JwtMiddleware::class)->group(function () {
    require __DIR__ . '/Secretary/appointment.php';
    require __DIR__ . '/Secretary/payment.php';
});

Route::prefix('doctor')->middleware(JwtMiddleware::class)->group(function () {
    require __DIR__ . '/Doctor/appointments.php';
    require __DIR__ . '/Doctor/patientInfo.php';
    require __DIR__ . '/Doctor/profile.php';
    require __DIR__ . '/Doctor/child.php';
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
    require __DIR__ . '/patient/payment.php';
});
