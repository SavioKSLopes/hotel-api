<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Hotel API',
    description: 'API REST para gerenciamento de hotéis, quartos, hóspedes, reservas, pagamentos, cupons e taxas.'
)]
#[OA\Server(
    url: 'http://localhost:8080',
    description: 'Servidor local'
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Sanctum',
    description: 'Informe o token no formato: Bearer {token}'
)]
#[OA\Schema(
    schema: 'Room',
    title: 'Quarto',
    required: ['id', 'hotel_id', 'external_id', 'name'],
    properties: [
        new OA\Property(
            property: 'id',
            type: 'integer',
            example: 1
        ),
        new OA\Property(
            property: 'hotel_id',
            type: 'integer',
            example: 1
        ),
        new OA\Property(
            property: 'external_id',
            type: 'string',
            example: 'ROOM-101'
        ),
        new OA\Property(
            property: 'name',
            type: 'string',
            example: 'Quarto Standard 101'
        ),
        new OA\Property(
            property: 'created_at',
            type: 'string',
            format: 'date-time',
            example: '2026-10-06T14:30:00.000000Z'
        ),
        new OA\Property(
            property: 'updated_at',
            type: 'string',
            format: 'date-time',
            example: '2026-10-06T14:30:00.000000Z'
        )
    ]
)]

#[OA\Schema(
    schema: 'Payment',
    title: 'Pagamento',
    required: [
        'id',
        'reserve_id',
        'payment_method_id',
        'value',
        'status',
    ],
    properties: [
        new OA\Property(
            property: 'id',
            type: 'integer',
            example: 1
        ),
        new OA\Property(
            property: 'reserve_id',
            type: 'integer',
            example: 1
        ),
        new OA\Property(
            property: 'payment_method_id',
            type: 'integer',
            example: 1
        ),
        new OA\Property(
            property: 'value',
            type: 'number',
            format: 'float',
            example: 250.00
        ),
        new OA\Property(
            property: 'status',
            type: 'string',
            enum: ['pending', 'paid', 'refunded', 'failed'],
            example: 'paid'
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
                'transaction_id' => 'pi_123456',
            ]
        ),
        new OA\Property(
            property: 'paid_at',
            type: 'string',
            format: 'date-time',
            nullable: true,
            example: '2026-10-06T22:30:00.000000Z'
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
)]

#[OA\Schema(
    schema: 'HotelUser',
    title: 'Usuário do hotel',
    required: ['id', 'hotel_id', 'name', 'email', 'role'],
    properties: [
        new OA\Property(
            property: 'id',
            type: 'integer',
            example: 1
        ),
        new OA\Property(
            property: 'hotel_id',
            type: 'integer',
            example: 1
        ),
        new OA\Property(
            property: 'name',
            type: 'string',
            example: 'Maria Souza'
        ),
        new OA\Property(
            property: 'email',
            type: 'string',
            format: 'email',
            example: 'maria.souza@hotel.com'
        ),
        new OA\Property(
            property: 'role',
            type: 'string',
            enum: ['owner', 'manager', 'receptionist'],
            example: 'manager'
        ),
        new OA\Property(
            property: 'is_active',
            type: 'boolean',
            example: true
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
)]

final class OpenApi
{
}
