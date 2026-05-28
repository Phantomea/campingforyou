<?php

use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/services', [\App\Http\Controllers\Api\ServiceController::class, 'index']);
Route::get('/services/{service:slug}', [\App\Http\Controllers\Api\ServiceController::class, 'show']);
Route::get('/addon-services', [\App\Http\Controllers\Api\AddonServiceController::class, 'index']);
Route::get('/bookings/availability', [\App\Http\Controllers\Api\BookingController::class, 'availability']);
Route::get('/bookings/preview', [\App\Http\Controllers\Api\BookingController::class, 'preview']);
Route::get('/bookings/blocked-days', [\App\Http\Controllers\Api\BookingController::class, 'blockedDays']);
Route::post('/bookings', [\App\Http\Controllers\Api\BookingController::class, 'store']);
Route::get('/bookings/status/{token}', [\App\Http\Controllers\Api\BookingController::class, 'status']);
Route::get('/pricing', [\App\Http\Controllers\Api\PricingController::class, 'index']);
Route::get('/pages/{page:slug}', [\App\Http\Controllers\Api\PageController::class, 'show']);
Route::get('/settings/public', [\App\Http\Controllers\Api\SettingController::class, 'public']);
Route::get('/gallery', [\App\Http\Controllers\Api\GalleryController::class, 'index']);

// Auth routes
Route::post('/login', [\App\Http\Controllers\Api\AuthController::class, 'login']);
Route::post('/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/user', [\App\Http\Controllers\Api\AuthController::class, 'user'])->middleware('auth:sanctum');

// Admin routes (owner + super_admin)
Route::middleware(['auth:sanctum', 'role:owner,super_admin'])->prefix('admin')->group(function () {
    // Services
    Route::apiResource('services', \App\Http\Controllers\Admin\ServiceController::class);
    Route::post('services/{service}/images', [\App\Http\Controllers\Admin\ServiceController::class, 'uploadImages']);
    Route::delete('services/{service}/images', [\App\Http\Controllers\Admin\ServiceController::class, 'deleteImage']);

    // Pricing
    Route::apiResource('pricing', \App\Http\Controllers\Admin\PricingController::class);
    Route::apiResource('pricing-categories', \App\Http\Controllers\Admin\PricingCategoryController::class);

    // Pages
    Route::apiResource('pages', \App\Http\Controllers\Admin\PageController::class);

    // Booking blocks
    Route::get('booking-blocks', [\App\Http\Controllers\Admin\BookingBlockController::class, 'index']);
    Route::post('booking-blocks', [\App\Http\Controllers\Admin\BookingBlockController::class, 'store']);
    Route::delete('booking-blocks/{bookingBlock}', [\App\Http\Controllers\Admin\BookingBlockController::class, 'destroy']);

    // Bookings
    Route::get('bookings', [\App\Http\Controllers\Admin\BookingController::class, 'index']);
    Route::get('bookings/stats', [\App\Http\Controllers\Admin\BookingController::class, 'stats']);
    Route::get('bookings/{booking}', [\App\Http\Controllers\Admin\BookingController::class, 'show']);
    Route::patch('bookings/{booking}/status', [\App\Http\Controllers\Admin\BookingController::class, 'updateStatus']);
    Route::delete('bookings/{booking}', [\App\Http\Controllers\Admin\BookingController::class, 'destroy']);

    // Addon services
    Route::apiResource('addon-services', \App\Http\Controllers\Admin\AddonServiceController::class)->except(['show']);

    // Settings
    Route::get('settings', [\App\Http\Controllers\Admin\SettingController::class, 'index']);
    Route::put('settings', [\App\Http\Controllers\Admin\SettingController::class, 'update']);
});

// Super admin only routes
Route::middleware(['auth:sanctum', 'role:super_admin'])->prefix('admin')->group(function () {
    Route::apiResource('users', \App\Http\Controllers\Admin\UserController::class);
});
