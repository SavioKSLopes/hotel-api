<?php

namespace App\Http\Controllers;

use App\Http\Resources\PaymentResource;
use App\Models\Hotel;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
    ) {
    }

    public function index(int $hotelId): AnonymousResourceCollection
    {
        $hotel = Hotel::findOrFail($hotelId);

        $payments = $this->paymentService->listPaymentsByHotel($hotel)
            ->load(['reserve', 'paymentMethod']);

        return PaymentResource::collection($payments);
    }

    public function show(int $hotelId, int $paymentId): JsonResponse
    {
        $hotel = Hotel::findOrFail($hotelId);

        $payment = $this->paymentService->findPaymentByHotel($hotel, $paymentId);

        return response()->json(new PaymentResource($payment));
    }

    public function update(Request $request, int $hotelId, int $paymentId): JsonResponse
    {
        $hotel = Hotel::findOrFail($hotelId);

        $updater = $request->user();

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,paid,refunded,failed'],
            'external_reference' => ['nullable', 'string', 'max:255'],
            'metadata' => ['nullable', 'array'],
        ]);

        $payment = $this->paymentService->updatePayment(
            $validated,
            $hotel,
            $paymentId,
            $updater
        );

        return response()->json(new PaymentResource($payment));
    }
}
