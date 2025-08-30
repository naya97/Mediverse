<?php

use App\Http\Controllers\Doctor\ChildController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware([JwtMiddleware::class])->group(function () {
    Route::controller(ChildController::class)->group(function () {
        // Route::get('showChildrenRecords', 'showChildrenRecords');
        Route::get('showChildRecord', 'showChildRecord');
        Route::post('addChildRecords', 'addChildRecords');
        Route::post('editChildRecords', 'editChildRecords');
        Route::get('showVaccines', 'showVaccines');
        Route::get('showChildren', 'showChildren');
        Route::get('showVaccineRecords', 'showVaccineRecords');
        Route::get('showVaccineRecordsDetails', 'showVaccineRecordsDetails');
        Route::post('editVaccineRecordInfo', 'editVaccineRecordInfo');
    });
});
