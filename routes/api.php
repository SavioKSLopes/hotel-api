<?php

use App\Http\Controllers\Api\ReserveController;
use App\Http\Controllers\Api\RoomController;
use Illuminate\Support\Facades\Route;

Route::apiResource('rooms', RoomController::class);

Route::post('reserves', [ReserveController::class, 'store'])
    ->name('reserves.store');
