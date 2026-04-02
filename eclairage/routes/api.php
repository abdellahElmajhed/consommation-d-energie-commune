<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompteurController;
use App\Http\Controllers\Api\ConsommationController;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);

    Route::middleware('approved')->group(function () {
        Route::apiResource('compteurs', CompteurController::class);
        Route::apiResource('consommations', ConsommationController::class);
    });

    Route::middleware('admin')->group(function () {
        Route::get('admin/users', [AuthController::class, 'indexUsers']);
        Route::put('admin/users/{user}/access', [AuthController::class, 'updateUserAccess']);
    });
});
