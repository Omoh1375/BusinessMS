<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BranchController;

Route::prefix('v1')->group(function () {

    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
       
    });
     Route::middleware(['auth:sanctum', 'business'])->group(function () {
        Route::get('/current-business', [AuthController::class, 'currentBusiness']);
    });

    Route::middleware(['auth:sanctum', 'business'])->group(function () {
        Route::get('/current-business', [AuthController::class, 'currentBusiness']);

        Route::apiResource('branches', BranchController::class);
    });

});