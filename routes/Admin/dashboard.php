<?php

use App\Http\Controllers\Admin\DashBoardController;
use App\Http\Controllers\Admin\DiscountController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware([JwtMiddleware::class])->group(function () {
    Route::controller(DashBoardController::class)->group(function () {
        Route::get('/showAllAppointments', 'showAllAppointments');
        Route::get('/filteringAppointmentByDoctor', 'filteringAppointmentByDoctor');
        Route::post('/filteringAppointmentByStatus', 'filteringAppointmentByStatus');
        Route::get('/showPaymentDetails', 'showPaymentDetails');
        Route::get('/showPaymentDetailsByDoctor', 'showPaymentDetailsByDoctor');
        Route::post('/showPaymentDetailsByDate', 'showPaymentDetailsByDate');
        Route::get('/showAllPayments', 'showAllPayments');
        Route::get('/showPatients', 'showPatients');
        Route::get('/showDoctorPatients', 'showDoctorPatients');
        Route::get('/showPatientDetails', 'showPatientDetails');
        Route::delete('/deletePatient', 'deletePatient');
    });
    Route::controller(ReportController::class)->group(function () {
        Route::get('/showAllReports', 'showAllReports');
        Route::get('/showReport', 'showReport');
    });
});
