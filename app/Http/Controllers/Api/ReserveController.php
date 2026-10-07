<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReserveRequest;
use App\Services\ReserveService;
use Illuminate\Http\JsonResponse;

class ReserveController extends Controller
{
    public function store(
        StoreReserveRequest $request,
        ReserveService $reserveService
    ): JsonResponse {
        $reserve = $reserveService->createReserve($request->validated());

        return response()->json($reserve, 201);
    }
}
