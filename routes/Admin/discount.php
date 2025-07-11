<?php

use App\Http\Controllers\Admin\DiscountController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;

// Route::middleware([JwtMiddleware::class])->group(function () {
//     Route::controller(DiscountController::class)->group(function () {
//         Route::post('/addDiscount', 'addDiscount');
//         Route::post('/editDiscount', 'editDiscount');
//         Route::delete('/deleteDiscount', 'deleteDiscount');
//         Route::get('/showDiscounts', 'showDiscounts');
//     });
// });
