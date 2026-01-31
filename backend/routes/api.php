<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/tickets', [TicketController::class, 'index']);
    Route::post('/tickets', [TicketController::class, 'store']);

    Route::middleware('role:agent,admin')->group(function () {
        Route::patch('/tickets/{ticket}/status', [TicketController::class, 'updateStatus']);
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
    });
});

