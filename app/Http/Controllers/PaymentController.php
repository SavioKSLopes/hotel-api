<?php

namespace App\Http\Controllers;

use App\Http\Resources\PaymentResource;
use App\Models\Hotel;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
    ) {}

    public function index(int $hotelId): AnonymousResourceCollection
    {
        $hotel = Hotel::findOrFail($hotelId);

        $payments = $this->paymentService->listPaymentsByHotel($hotel)
            ->load(['reserve', 'paymentMethod']);

        return PaymentResource::collection($payments);
    }

    public function show(int $hotelId, int $paymentId): PaymentResource
    {
        $hotel = Hotel::findOrFail($hotelId);

        $payment = $this->paymentService->findPaymentByHotel($hotel, $paymentId);

        return new PaymentResource($payment);
    }

    public function update(Request $request, int $hotelId, int $paymentId): PaymentResource
    {
        $hotel = Hotel::findOrFail($hotelId);

        $updater = $request->user();

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,paid,refunded,failed'],
            'value' => ['nullable', 'numeric', 'min:0'],
            'external_reference' => ['nullable', 'string', 'max:255'],
            'metadata' => ['nullable', 'array'],
        ]);

        $payment = $this->paymentService->updatePayment(
            $validated,
            $hotel,
            $paymentId,
            $updater
        );

        return new PaymentResource($payment);
    }

    public function store(Request $request, int $hotelId): JsonResponse
    {
        $data = $request->validate([
            'reserve_id' => ['required', 'integer', 'exists:reserves,id'],
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
            'value' => ['required', 'numeric', 'min:0'],
            'status' => ['nullable', 'string'],
            'external_reference' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
            'paid_at' => ['nullable', 'date'],
        ]);

        $payment = $this->paymentService->create(
            $hotelId,
            $data
        );

        return response()->json([
            'data' => $payment,
        ], 201);
    }
}
