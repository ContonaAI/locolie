<?php

use App\Http\Controllers\Api\BrowseController;
use App\Http\Controllers\Api\BusinessController;
use App\Http\Controllers\Api\PushController;
use App\Http\Controllers\Api\RedemptionController;
use Illuminate\Support\Facades\Route;

/*
 | GoLocal JSON API (prototype).
 | Stateless. Browsing is public; retailer actions are scoped by a per-business
 | owner secret returned at signup. Sanctum token auth is a thin future add for native iOS.
 */

// Browse
Route::get('/categories', [BrowseController::class, 'categories']);
Route::get('/businesses', [BrowseController::class, 'businesses']);
Route::get('/businesses/by-token/{token}', [BrowseController::class, 'byToken']);
Route::get('/businesses/{business:slug}', [BrowseController::class, 'business']);

// Retailer (owner-secret scoped)
Route::get('/places/search', [BusinessController::class, 'placesSearch']);
Route::post('/businesses', [BusinessController::class, 'register']);
Route::get('/businesses/secret/{secret}/offers', [BusinessController::class, 'offers']);
Route::post('/businesses/secret/{secret}/offers', [BusinessController::class, 'storeOffer']);
Route::delete('/businesses/secret/{secret}/offers/{offer}', [BusinessController::class, 'destroyOffer']);
Route::get('/businesses/secret/{secret}/redemptions', [BusinessController::class, 'redemptions']);

// Web push (shopper opt-in)
Route::post('/push/subscribe', [PushController::class, 'subscribe']);

// Redemption
Route::post('/offers/{offer}/redeem', [RedemptionController::class, 'redeem']);
Route::post('/redemptions/verify', [RedemptionController::class, 'verify']);
Route::get('/redemptions/{code}', [RedemptionController::class, 'show']);
