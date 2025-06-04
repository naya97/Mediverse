<?php


use App\Http\Controllers\Admin\PharmacyController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware([JwtMiddleware::class])->group(function () {
    Route::controller(PharmacyController::class)->group(function () {
        Route::post('add_Pharmacy', 'add');
        Route::post('update_Pharmacy', 'update');
        Route::post('delete_Pharmacy', 'delete');
        Route::get('showAllPharmacies', 'showAllPharmacies');
        Route::post('searchPharmacy', 'searchPharmacy');
        Route::get('getPharmacyById', 'getPharmacyById');
    });
});
