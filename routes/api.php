<?php
// routes/api.php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\CarCommandController;
use App\Http\Controllers\GeoFenceController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (require token)
Route::middleware('token.auth')->group(function () {
    // Authentication
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/check-auth', [AuthController::class, 'checkAuth']);
    
    // Car list (no specific car needed)
    Route::get('/cars', [CarController::class, 'index']);
    
    // Car-specific routes with authorization middleware
    Route::prefix('cars/{carId}')->middleware('can.access.car')->group(function () {
        // View permissions only
        Route::middleware('can.access.car:view')->group(function () {
            Route::get('/live', [CarController::class, 'liveLocation']);
            Route::get('/locations', [CarController::class, 'locationHistory']);
            Route::get('/door-status', [CarController::class, 'doorStatus']);
            Route::get('/fuel-cutoff', [CarController::class, 'fuelCutoff']);
            Route::get('/geofences', [GeoFenceController::class, 'index']);
        });
        
        // Control permissions required
        Route::middleware('can.access.car:control')->group(function () {
            Route::post('/tracking', [CarController::class, 'updateTracking']);
            Route::post('/alarm', [CarController::class, 'updateAlarm']);
            
            // Command routes
            Route::post('/commands', [CarCommandController::class, 'sendCommand']);
            Route::get('/commands', [CarCommandController::class, 'getCommands']);
            Route::post('/geofences', [GeoFenceController::class, 'store']);
            Route::put('/geofences/{fenceId}', [GeoFenceController::class, 'update']);
            Route::delete('/geofences/{fenceId}', [GeoFenceController::class, 'destroy']);
        });
    });
});

// ESP32 command endpoints (no auth, uses IMEI)
Route::prefix('tracker')->group(function () {
    Route::get('/commands', [CarCommandController::class, 'getPendingCommands']);
    Route::post('/commands/{commandId}/status', [CarCommandController::class, 'updateCommandStatus']);
});