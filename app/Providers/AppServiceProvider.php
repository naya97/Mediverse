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
    }
}
