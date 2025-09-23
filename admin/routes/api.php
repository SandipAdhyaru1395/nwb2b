<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\SettingController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::get('/products',[ProductController::class, 'index']);

// Checkout API endpoint
Route::post('/checkout',[OrderController::class, 'store']);

// Recent Orders API endpoint
Route::get('/orders',[OrderController::class, 'index']);

// Settings endpoint (company logo, etc.)
Route::get('/settings', [SettingController::class, 'show']);
