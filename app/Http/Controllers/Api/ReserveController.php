<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReserveRequest;
use App\Services\ReserveService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class ReserveController extends Controller
{
    #[OA\Post(
        path: '/api/reserves',
        tags: ['Reservas'],
        summary: 'Cria uma reserva',
        description: 'Cria uma reserva para um quarto em um período informado. A data de check-out deve ser posterior à data de check-in. Um cupom pode ser informado opcionalmente.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: [
                    'external_id',
                    'hotel_id',
                    'room_id',
                    'guest_id',
                    'check_in',
                    'check_out',
                    'total',
                ],
                properties: [
                    new OA\Property(
                        property: 'external_id',
                        type: 'string',
                        maxLength: 50,
                        example: 'RES-0001',
                        description: 'Identificador externo e único da reserva'
                    ),
                    new OA\Property(
                        property: 'hotel_id',
                        type: 'integer',
                        example: 1,
                        description: 'Identificador do hotel'
                    ),
                    new OA\Property(
                        property: 'room_id',
                        type: 'integer',
                        example: 1,
                        description: 'Identificador do quarto'
                    ),
                    new OA\Property(
                        property: 'guest_id',
                        type: 'integer',
                        example: 1,
                        description: 'Identificador do hóspede'
                    ),
                    new OA\Property(
                        property: 'check_in',
                        type: 'string',
                        format: 'date',
                        example: '2026-10-20',
                        description: 'Data de entrada'
                    ),
                    new OA\Property(
                        property: 'check_out',
                        type: 'string',
                        format: 'date',
                        example: '2026-10-23',
                        description: 'Data de saída; deve ser posterior ao check-in'
                    ),
                    new OA\Property(
                        property: 'total',
                        type: 'number',
                        format: 'float',
                        minimum: 0,
                        example: 600.00,
                        description: 'Valor total inicialmente informado para a reserva'
                    ),
                    new OA\Property(
                        property: 'coupon_code',
                        type: 'string',
                        maxLength: 50,
                        nullable: true,
                        example: 'PROMO10',
                        description: 'Código de cupom de desconto, quando aplicável'
                    )
                ],
                example: [
                    'external_id' => 'RES-0001',
                    'hotel_id' => 1,
                    'room_id' => 1,
                    'guest_id' => 1,
                    'check_in' => '2026-10-20',
                    'check_out' => '2026-10-23',
                    'total' => 600.00,
                    'coupon_code' => 'PROMO10',
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Reserva criada com sucesso',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'id',
                            type: 'integer',
                            example: 1
                        ),
                        new OA\Property(
                            property: 'external_id',
                            type: 'string',
                            example: 'RES-0001'
                        ),
                        new OA\Property(
                            property: 'hotel_id',
                            type: 'integer',
                            example: 1
                        ),
                        new OA\Property(
                            property: 'room_id',
                            type: 'integer',
                            example: 1
                        ),
                        new OA\Property(
                            property: 'guest_id',
                            type: 'integer',
                            example: 1
                        ),
                        new OA\Property(
                            property: 'coupon_id',
                            type: 'integer',
                            nullable: true,
                            example: 1
                        ),
                        new OA\Property(
                            property: 'check_in',
                            type: 'string',
                            format: 'date',
                            example: '2026-10-20'
                        ),
                        new OA\Property(
                            property: 'check_out',
                            type: 'string',
                            format: 'date',
                            example: '2026-10-23'
                        ),
                        new OA\Property(
                            property: 'subtotal',
                            type: 'number',
                            format: 'float',
                            example: 600.00
                        ),
                        new OA\Property(
                            property: 'discount_total',
                            type: 'number',
                            format: 'float',
                            example: 60.00
                        ),
                        new OA\Property(
                            property: 'fee_total',
                            type: 'number',
                            format: 'float',
                            example: 20.00
                        ),
                        new OA\Property(
                            property: 'total',
                            type: 'number',
                            format: 'float',
                            example: 560.00
                        ),
                        new OA\Property(
                            property: 'created_at',
                            type: 'string',
                            format: 'date-time'
                        ),
                        new OA\Property(
                            property: 'updated_at',
                            type: 'string',
                            format: 'date-time'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Dados inválidos, entidades inexistentes, período inválido ou quarto indisponível',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'The check out field must be a date after check in.'
                        ),
                        new OA\Property(
                            property: 'errors',
                            type: 'object',
                            example: [
                                'check_out' => [
                                    'The check out field must be a date after check in.'
                                ]
                            ]
                        )
                    ]
                )
            )
        ]
    )]
    public function store(
        StoreReserveRequest $request,
        ReserveService $reserveService
    ): JsonResponse {
        $reserve = $reserveService->createReserve($request->validated());

        return response()->json($reserve, 201);
    }
}
