<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TicketCommentController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::apiResource('users', UserController::class)
            ->only(['index', 'store', 'update']);

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

        Route::get(
            'technicians',
            [TicketController::class, 'technicians']
        );

        Route::patch(
            'tickets/{ticket}/assign',
            [TicketController::class, 'assign']
        );

        Route::patch(
            'tickets/{ticket}/assign-technician',
            [TicketController::class, 'assignTechnician']
        );

        Route::patch(
            'tickets/{ticket}/resolve',
            [TicketController::class, 'resolve']
        );

        Route::patch(
            'tickets/{ticket}/close',
            [TicketController::class, 'close']
        );

        Route::get(
            'tickets/{ticket}/comments',
            [TicketCommentController::class, 'index']
        );

        Route::post(
            'tickets/{ticket}/comments',
            [TicketCommentController::class, 'store']
        );
    });
});
