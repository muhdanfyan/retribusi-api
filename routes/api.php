<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OpdController;
use App\Http\Controllers\RetributionTypeController;
use App\Http\Controllers\TaxpayerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\ZoneController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/opd/register', [OpdController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/citizen/login', [AuthController::class, 'citizenLogin']);
Route::post('/citizen/register', [AuthController::class, 'registerCitizen']);
Route::get('/retribution-types', [RetributionTypeController::class, 'index']); // Public access
Route::get('/opds', [OpdController::class, 'index']); // Public access
Route::get('/citizen/bills', [BillController::class, 'citizenBills']); // Public access for demo

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/citizen/change-password', [AuthController::class, 'changeCitizenPassword']);
    
    // Citizen Service Registration
    Route::prefix('citizen/services')->group(function () {
        Route::get('/', [\App\Http\Controllers\CitizenServiceController::class, 'index']);
        Route::get('/{id}', [\App\Http\Controllers\CitizenServiceController::class, 'show']);
        Route::post('/{id}/register', [\App\Http\Controllers\CitizenServiceController::class, 'register']);
        Route::get('/{id}/bills', [\App\Http\Controllers\CitizenServiceController::class, 'bills']);
    });
    
    // Retribution Types (OPD-scoped)
    Route::apiResource('retribution-types', RetributionTypeController::class)->except(['index']);
    
    // Taxpayers (OPD-scoped)
    Route::apiResource('taxpayers', TaxpayerController::class);

    // Billings
    Route::post('/bills/bulk', [BillController::class, 'bulkStore']);
    Route::apiResource('bills', BillController::class)->except(['update', 'destroy']);
    
    // Verifications
    Route::put('/verifications/{verification}/status', [VerificationController::class, 'updateStatus']);
    Route::apiResource('verifications', VerificationController::class)->only(['index', 'show', 'store']);

    // Zones
    Route::apiResource('zones', ZoneController::class);

    // OPD Management (super_admin only in controller)
    Route::apiResource('opds', OpdController::class)->except(['create', 'edit', 'index']);

    // User Management
    Route::apiResource('users', UserController::class);

    // Dashboard Analytics
    Route::prefix('dashboard')->group(function () {
        Route::get('/stats', [DashboardController::class, 'getStats']);
        Route::get('/revenue-trend', [DashboardController::class, 'getRevenueTrend']);
        Route::get('/map-potentials', [DashboardController::class, 'getMapPotentials']);
    });
});
