<?php

use App\Http\Controllers\Home\HomeController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;



Route::middleware([JwtMiddleware::class])->group(function () {
    Route::controller(HomeController::class)->group(function () {
        Route::get('/showDoctors', 'showDoctors');
        Route::get('/showDoctorDetails', 'showDoctorDetails');
        Route::get('/showClinicDoctors', 'showClinicDoctors');
        Route::post('/searchDoctor', 'searchDoctor');
        Route::get('/showClinics', 'showClinics');
        Route::get('showAllPharmacies', 'showAllPharmacies');
        Route::post('searchPharmacy', 'searchPharmacy');
        Route::get('getPharmacyById', 'getPharmacyById');
    });
});
