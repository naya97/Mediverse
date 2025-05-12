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
            ->prefix('lab_pharmacy')
            ->group(base_path('routes/LabAndPharmacyAPI.php'));
        Route::middleware('api')
            ->prefix('Admin')
            ->group(base_path('routes/Admin/lab_phar.php'));
        Route::middleware('api')
            ->prefix('Admin')
            ->group(base_path('routes/Admin/doctor.php'));
        Route::middleware('api')
            ->prefix('Home')
            ->group(base_path('routes/Home/home.php'));
        Route::middleware('api')
            ->prefix('Doctor')
            ->group(base_path('routes/Doctor/profile.php'));
        Route::middleware('api')
            ->prefix('patient')
            ->group(base_path('routes/patient/reservation.php'));
    }
}
