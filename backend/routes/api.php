<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::post('/register', [AuthController::class, 'register']);

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

        Route::apiResource('tickets', TicketController::class)
            ->only(['index', 'store', 'show']);

        Route::patch(
            'tickets/{ticket}/assign',
            [TicketController::class, 'assign']
        );

        Route::patch(
            'tickets/{ticket}/resolve',
            [TicketController::class, 'resolve']
        );

        Route::patch(
            'tickets/{ticket}/close',
            [TicketController::class, 'close']
        );
    });
});
