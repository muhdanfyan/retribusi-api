<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OpdController;
use App\Http\Controllers\RetributionTypeController;
use App\Http\Controllers\TaxpayerController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/opd/register', [OpdController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // Retribution Types (OPD-scoped)
    Route::apiResource('retribution-types', RetributionTypeController::class);
    
    // Taxpayers (OPD-scoped)
    Route::apiResource('taxpayers', TaxpayerController::class);
    
    // OPD Management (super_admin only in controller)
    Route::apiResource('opds', OpdController::class)->except(['create', 'edit']);
});
