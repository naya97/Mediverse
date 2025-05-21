<?php

use App\Http\Controllers\Patient\Medical_Analysis\MedicalAnalysisController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware([JwtMiddleware::class])->group(function () {
    Route::controller(MedicalAnalysisController::class)->group(function () {
        Route::post('addAnalysis', 'addAnalysis');
        Route::post('deleteAnalysis', 'deleteAnalysis');
        Route::get('showAnalysis', 'showAnalysis');
    });
});
