<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReserveRequest;
use App\Http\Resources\ReserveResource;
use App\Models\Reserve;
use Illuminate\Http\JsonResponse;


class ReserveController extends Controller
{
    public function store(StoreReserveRequest $request): JsonResponse
    {
        $reserve = Reserve::create($request->validated());

        return (new ReserveResource($reserve))->response()->setStatusCode(201);
    }
}
