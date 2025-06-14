<?php

use App\Http\Controllers\LabTech\AnalysisController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware([JwtMiddleware::class])->group(function () {
    Route::controller(AnalysisController::class)->group(function () {
        Route::post('addAnalyse', 'addAnalyse');
        Route::post('showAllAnalysis', 'showAllAnalysis');
        Route::get('showAnalyse', 'showAnalyse');
        Route::get('showClinics', 'showClinics');
        Route::post('addAnalyseResult', 'addAnalyseResult');
        Route::post('searchAnalyseByName', 'searchAnalyseByName');
        Route::post('searchAnalyseByPatientNum', 'searchAnalyseByPatientNum');
        Route::post('addBill', 'addBill');
    });
});
