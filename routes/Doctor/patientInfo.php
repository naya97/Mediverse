<?php

use App\Http\Controllers\Doctor\PatientInfoController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware([JwtMiddleware::class])->group(function () {
    Route::controller(PatientInfoController::class)->group(function () {
        Route::post('requestAnalyse', 'requestAnalyse');
        Route::post('addPrescription', 'addPrescription');
        Route::post('addMedicine', 'addMedicine');
        Route::post('completPrescription', 'completPrescription');
        Route::get('showPatientAnalysis', 'showPatientAnalysis');
        Route::post('showPatientAnalysisByStatus', 'showPatientAnalysisByStatus');
        Route::get('showClinics', 'showClinics');
        Route::post('showPatientAnalysisByClinic', 'showPatientAnalysisByClinic');
        Route::post('addMedicalInfo', 'addMedicalInfo');
        Route::get('showPatientProfile', 'showPatientProfile');
        Route::get('patientsRecord', 'patientsRecord');
        Route::post('searchPatient', 'searchPatient');
    });
});
