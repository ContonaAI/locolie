<?php

use App\Http\Controllers\Api\BrowseController;
use App\Http\Controllers\Api\BusinessController;
use App\Http\Controllers\Api\PushController;
use App\Http\Controllers\Api\RedemptionController;
use App\Http\Controllers\Api\SyncController;
use Illuminate\Support\Facades\Route;

/*
 | Locolie JSON API (prototype).
 | Stateless. Browsing is public; retailer actions are scoped by a per-business
 | owner secret returned at signup. Sanctum token auth is a thin future add for native iOS.
 */

// Baseline throttle for the whole JSON API (60/min per IP, see the "api"
// limiter in AppServiceProvider). Abuse-prone endpoints get a tighter cap below.
Route::middleware('throttle:api')->group(function () {
    // Browse
    Route::get('/categories', [BrowseController::class, 'categories']);
    Route::get('/businesses', [BrowseController::class, 'businesses']);
    Route::get('/businesses/by-token/{token}', [BrowseController::class, 'byToken']);
    Route::get('/businesses/{business:slug}', [BrowseController::class, 'business']);

    // Retailer (owner-secret scoped)
    // Tighter cap on the billable Google Places proxy and the register endpoint.
    Route::middleware('throttle:20,1')->group(function () {
        Route::get('/places/search', [BusinessController::class, 'placesSearch']);
        Route::post('/businesses', [BusinessController::class, 'register']);
    });
    Route::get('/businesses/secret/{secret}/offers', [BusinessController::class, 'offers']);
    Route::post('/businesses/secret/{secret}/offers', [BusinessController::class, 'storeOffer']);
    Route::delete('/businesses/secret/{secret}/offers/{offer}', [BusinessController::class, 'destroyOffer']);
    Route::get('/businesses/secret/{secret}/redemptions', [BusinessController::class, 'redemptions']);

    // Web push (shopper opt-in)
    Route::post('/push/subscribe', [PushController::class, 'subscribe']);
    // Native push: iOS / Android apps register their FCM / APNs device token here.
    Route::post('/devices/register', [PushController::class, 'registerDevice']);
    Route::delete('/devices/{token}', [PushController::class, 'unregisterDevice']);

    // Redemption - tighter cap on the redeem + verify endpoints.
    Route::middleware('throttle:20,1')->group(function () {
        Route::post('/offers/{offer}/redeem', [RedemptionController::class, 'redeem']);
        Route::post('/redemptions/verify', [RedemptionController::class, 'verify']);
    });
    Route::get('/redemptions/{code}', [RedemptionController::class, 'show']);

    // Local -> production data sync (token-guarded). See SyncController + `php artisan sync:push`.
    Route::middleware('sync.token')->prefix('sync')->group(function () {
        Route::get('/status', [SyncController::class, 'status']);
        Route::post('/data', [SyncController::class, 'data']);
        Route::post('/image', [SyncController::class, 'image']);
    });
});
