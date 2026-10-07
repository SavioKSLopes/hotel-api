<?php

use App\Http\Controllers\Api\ReserveController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\HotelUserController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('rooms', RoomController::class);
});

Route::post('reserves', [ReserveController::class, 'store'])
    ->name('reserves.store');

Route::middleware(['auth:sanctum', 'role:manager'])
    ->prefix('hotels/{hotelId}')
    ->group(function () {
        Route::apiResource('users', HotelUserController::class, [
            'parameters' => [
                'users' => 'userId',
            ],
        ]);
    });

Route::middleware(['auth:sanctum', 'role:manager'])
    ->prefix('hotels/{hotelId}')
    ->group(function () {
        Route::apiResource('payments', PaymentController::class, [
            'parameters' => [
                'payments' => 'paymentId',
            ],
        ])->except(['destroy']);
    });
