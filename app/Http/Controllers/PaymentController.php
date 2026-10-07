<?php

namespace App\Http\Controllers;

use App\Http\Resources\PaymentResource;
use App\Models\Hotel;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
    ) {}

    #[OA\Get(
        path: '/api/hotels/{hotelId}/payments',
        tags: ['Pagamentos'],
        summary: 'Lista os pagamentos de um hotel',
        description: 'Retorna todos os pagamentos vinculados às reservas de um hotel.',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'hotelId',
                in: 'path',
                required: true,
                description: 'ID do hotel',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Pagamentos retornados com sucesso',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Payment')
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Não autenticado'
            ),
            new OA\Response(
                response: 403,
                description: 'Sem permissão para acessar os pagamentos deste hotel'
            ),
            new OA\Response(
                response: 404,
                description: 'Hotel não encontrado'
            )
        ]
    )]
    public function index(int $hotelId): AnonymousResourceCollection
    {
        $hotel = Hotel::findOrFail($hotelId);

        $payments = $this->paymentService->listPaymentsByHotel($hotel)
            ->load(['reserve', 'paymentMethod']);

        return PaymentResource::collection($payments);
    }

    #[OA\Post(
        path: '/api/hotels/{hotelId}/payments',
        tags: ['Pagamentos'],
        summary: 'Cria um pagamento',
        description: 'Registra um pagamento para uma reserva vinculada ao hotel informado.',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'hotelId',
                in: 'path',
                required: true,
                description: 'ID do hotel',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['reserve_id', 'payment_method_id', 'value'],
                properties: [
                    new OA\Property(
                        property: 'reserve_id',
                        type: 'integer',
                        example: 1,
                        description: 'ID da reserva que receberá o pagamento'
                    ),
                    new OA\Property(
                        property: 'payment_method_id',
                        type: 'integer',
                        example: 1,
                        description: 'ID do método de pagamento'
                    ),
                    new OA\Property(
                        property: 'value',
                        type: 'number',
                        format: 'float',
                        minimum: 0,
                        example: 250.00,
                        description: 'Valor do pagamento'
                    ),
                    new OA\Property(
                        property: 'status',
                        type: 'string',
                        example: 'pending',
                        description: 'Status inicial do pagamento'
                    ),
                    new OA\Property(
                        property: 'external_reference',
                        type: 'string',
                        nullable: true,
                        example: 'TXN-123456',
                        description: 'Referência externa da transação'
                    ),
                    new OA\Property(
                        property: 'metadata',
                        type: 'object',
                        nullable: true,
                        example: [
                            'gateway' => 'stripe',
                            'transaction_id' => 'pi_123456'
                        ],
                        description: 'Metadados adicionais do pagamento'
                    ),
                    new OA\Property(
                        property: 'paid_at',
                        type: 'string',
                        format: 'date-time',
                        nullable: true,
                        example: '2026-10-06T22:30:00Z',
                        description: 'Data e hora de confirmação do pagamento'
                    )
                ],
                example: [
                    'reserve_id' => 1,
                    'payment_method_id' => 1,
                    'value' => 250.00,
                    'status' => 'pending',
                    'external_reference' => 'TXN-123456',
                    'metadata' => [
                        'gateway' => 'stripe',
                        'transaction_id' => 'pi_123456',
                    ],
                    'paid_at' => '2026-10-06T22:30:00Z',
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Pagamento criado com sucesso',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/Payment'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Não autenticado'
            ),
            new OA\Response(
                response: 403,
                description: 'Sem permissão para criar pagamentos neste hotel'
            ),
            new OA\Response(
                response: 404,
                description: 'Hotel não encontrado'
            ),
            new OA\Response(
                response: 422,
                description: 'Dados inválidos'
            )
        ]
    )]
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

    #[OA\Get(
        path: '/api/hotels/{hotelId}/payments/{paymentId}',
        tags: ['Pagamentos'],
        summary: 'Busca um pagamento',
        description: 'Retorna um pagamento pertencente ao hotel informado.',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'hotelId',
                in: 'path',
                required: true,
                description: 'ID do hotel',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
            new OA\Parameter(
                name: 'paymentId',
                in: 'path',
                required: true,
                description: 'ID do pagamento',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Pagamento encontrado',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/Payment'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Não autenticado'
            ),
            new OA\Response(
                response: 403,
                description: 'Sem permissão para acessar o pagamento'
            ),
            new OA\Response(
                response: 404,
                description: 'Hotel ou pagamento não encontrado'
            )
        ]
    )]
    public function show(int $hotelId, int $paymentId): PaymentResource
    {
        $hotel = Hotel::findOrFail($hotelId);

        $payment = $this->paymentService->findPaymentByHotel($hotel, $paymentId);

        return new PaymentResource($payment);
    }

    #[OA\Put(
        path: '/api/hotels/{hotelId}/payments/{paymentId}',
        tags: ['Pagamentos'],
        summary: 'Atualiza um pagamento',
        description: 'Atualiza o status e, opcionalmente, o valor, a referência externa e os metadados de um pagamento.',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'hotelId',
                in: 'path',
                required: true,
                description: 'ID do hotel',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
            new OA\Parameter(
                name: 'paymentId',
                in: 'path',
                required: true,
                description: 'ID do pagamento',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['status'],
                properties: [
                    new OA\Property(
                        property: 'status',
                        type: 'string',
                        enum: ['pending', 'paid', 'refunded', 'failed'],
                        example: 'paid',
                        description: 'Novo status do pagamento'
                    ),
                    new OA\Property(
                        property: 'value',
                        type: 'number',
                        format: 'float',
                        minimum: 0,
                        nullable: true,
                        example: 250.00
                    ),
                    new OA\Property(
                        property: 'external_reference',
                        type: 'string',
                        nullable: true,
                        example: 'TXN-123456'
                    ),
                    new OA\Property(
                        property: 'metadata',
                        type: 'object',
                        nullable: true,
                        example: [
                            'gateway' => 'stripe',
                            'transaction_id' => 'pi_123456'
                        ]
                    )
                ],
                example: [
                    'status' => 'paid',
                    'value' => 250.00,
                    'external_reference' => 'TXN-123456',
                    'metadata' => [
                        'gateway' => 'stripe',
                        'transaction_id' => 'pi_123456',
                    ],
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Pagamento atualizado com sucesso',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/Payment'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Não autenticado'
            ),
            new OA\Response(
                response: 403,
                description: 'Sem permissão para atualizar o pagamento'
            ),
            new OA\Response(
                response: 404,
                description: 'Hotel ou pagamento não encontrado'
            ),
            new OA\Response(
                response: 422,
                description: 'Dados inválidos'
            )
        ]
    )]
    #[OA\Patch(
        path: '/api/hotels/{hotelId}/payments/{paymentId}',
        tags: ['Pagamentos'],
        summary: 'Atualiza parcialmente um pagamento',
        description: 'Atualiza o pagamento. O status é obrigatório conforme a regra atual da API.',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'hotelId',
                in: 'path',
                required: true,
                description: 'ID do hotel',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
            new OA\Parameter(
                name: 'paymentId',
                in: 'path',
                required: true,
                description: 'ID do pagamento',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['status'],
                properties: [
                    new OA\Property(
                        property: 'status',
                        type: 'string',
                        enum: ['pending', 'paid', 'refunded', 'failed'],
                        example: 'paid'
                    ),
                    new OA\Property(
                        property: 'value',
                        type: 'number',
                        format: 'float',
                        minimum: 0,
                        nullable: true,
                        example: 250.00
                    ),
                    new OA\Property(
                        property: 'external_reference',
                        type: 'string',
                        nullable: true,
                        example: 'TXN-123456'
                    ),
                    new OA\Property(
                        property: 'metadata',
                        type: 'object',
                        nullable: true
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Pagamento atualizado com sucesso',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/Payment'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Não autenticado'
            ),
            new OA\Response(
                response: 403,
                description: 'Sem permissão para atualizar o pagamento'
            ),
            new OA\Response(
                response: 404,
                description: 'Hotel ou pagamento não encontrado'
            ),
            new OA\Response(
                response: 422,
                description: 'Dados inválidos'
            )
        ]
    )]
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
}
