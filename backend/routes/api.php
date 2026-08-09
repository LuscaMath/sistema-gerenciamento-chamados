<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::apiResource('categories', CategoryController::class)
            ->except('destroy');

        Route::patch(
            'categories/{category}/deactivate',
            [CategoryController::class, 'deactivate']
        );
        
        Route::patch(
            'categories/{category}/activate',
            [CategoryController::class, 'activate']
        );
    });
});