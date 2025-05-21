<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Route::middleware('api')
            ->prefix('patient')
            ->group(base_path('routes/patient/api.php'));
        Route::middleware('api')
            ->prefix('pharmacy')
            ->group(base_path('routes/pharmacies.php'));
        Route::middleware('api')
            ->prefix('Admin')
            ->group(base_path('routes/Admin/pharmacies.php'));
        Route::middleware('api')
            ->prefix('Admin')
            ->group(base_path('routes/Admin/doctor.php'));
        Route::middleware('api')
            ->prefix('Admin')
            ->group(base_path('routes/Admin/clinic.php'));
        Route::middleware('api')
            ->prefix('Admin')
            ->group(base_path('routes/Admin/employee.php'));
        Route::middleware('api')
            ->prefix('Home')
            ->group(base_path('routes/Home/home.php'));
        Route::middleware('api')
            ->prefix('Doctor')
            ->group(base_path('routes/Doctor/profile.php'));
        Route::middleware('api')
            ->prefix('Doctor/patientInfo')
            ->group(base_path('routes/Doctor/patientInfo.php'));
        Route::middleware('api')
            ->prefix('Doctor/appointments')
            ->group(base_path('routes/Doctor/appointments.php'));
        Route::middleware('api')
            ->prefix('patient')
            ->group(base_path('routes/patient/reservation.php'));
        Route::middleware('api')
            ->prefix('patient')
            ->group(base_path('routes/patient/analysis.php'));
        Route::middleware('api')
            ->prefix('labTech')
            ->group(base_path('routes/LabTech/analysis.php'));
    }
}
