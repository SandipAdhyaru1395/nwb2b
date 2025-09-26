<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;


// Public auth endpoints

// Public endpoints
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::get('/settings', [SettingController::class, 'show']);
// Protected endpoints
Route::middleware('auth:sanctum')->group(function () {
    
    Route::get('/products',[ProductController::class, 'index']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Authenticated customer profile
    Route::get('/customer', [CustomerController::class, 'me']);

    // Checkout API endpoint
    Route::post('/checkout',[OrderController::class, 'store']);

    // Recent Orders API endpoint
    Route::get('/orders',[OrderController::class, 'index']);
});
