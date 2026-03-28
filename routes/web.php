<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\Admin\AccountController as AdminAccountController;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::get('me', [AuthController::class, 'me'])->middleware('auth:api');
});

Route::middleware('auth:api')->prefix('users')->group(function () {
    Route::get('me', [UserController::class, 'show']);
    Route::put('me', [UserController::class, 'update']);
    Route::patch('me/password', [UserController::class, 'updatePassword']);
});

Route::middleware('auth:api')->prefix('accounts')->group(function () {
    Route::get('/', [AccountController::class, 'index']);
    Route::post('/', [AccountController::class, 'store']);
    Route::get('{id}', [AccountController::class, 'show']);
    Route::post('{id}/co-owners', [AccountController::class, 'addCoOwner']);
    Route::delete('{id}/co-owners/{userId}', [AccountController::class, 'removeCoOwner']);
    Route::post('{id}/guardian', [AccountController::class, 'assignGuardian']);
    Route::patch('{id}/convert', [AccountController::class, 'convert']);
    Route::delete('{id}', [AccountController::class, 'destroy']);
});

Route::middleware('auth:api')->prefix('transfers')->group(function () {
    Route::post('/', [TransferController::class, 'store']);
    Route::get('{id}', [TransferController::class, 'show']);
});

Route::middleware('auth:api')->group(function () {
    Route::get('accounts/{id}/transactions', [TransactionController::class, 'index']);
    Route::get('transactions/{id}', [TransactionController::class, 'show']);
});

Route::middleware(['auth:api', 'admin'])->prefix('admin')->group(function () {
    Route::get('accounts', [AdminAccountController::class, 'index']);
    Route::patch('accounts/{id}/block', [AdminAccountController::class, 'block']);
    Route::patch('accounts/{id}/unblock', [AdminAccountController::class, 'unblock']);
    Route::patch('accounts/{id}/close', [AdminAccountController::class, 'close']);
});