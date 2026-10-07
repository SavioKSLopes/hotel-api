<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\Hotel;
use App\Models\User;
use App\Services\HotelUserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

class HotelUserController extends Controller
{
    public function __construct(
        private HotelUserService $HotelUserService,
    ) {}

    #[OA\Get(
        path: '/api/hotels/{hotelId}/users',
        tags: ['Usuários'],
        summary: 'Lista usuários de um hotel',
        description: 'Retorna os usuários associados ao hotel informado.',
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
                description: 'Usuários retornados com sucesso',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/HotelUser')
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
                description: 'Sem permissão para acessar os usuários deste hotel'
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
        $users = $this->HotelUserService->listUsersByHotel($hotel);

        return UserResource::collection($users);
    }

    #[OA\Post(
        path: '/api/hotels/{hotelId}/users',
        tags: ['Usuários'],
        summary: 'Cria um usuário no hotel',
        description: 'Cria um novo usuário associado ao hotel com uma das funções permitidas: owner, manager ou receptionist.',
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
                required: ['name', 'email', 'password', 'role'],
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        maxLength: 255,
                        example: 'Maria Souza',
                        description: 'Nome completo do usuário'
                    ),
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        example: 'maria.souza@hotel.com',
                        description: 'E-mail do usuário'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        format: 'password',
                        minLength: 8,
                        example: 'senha-segura-123',
                        description: 'Senha com no mínimo 8 caracteres'
                    ),
                    new OA\Property(
                        property: 'role',
                        type: 'string',
                        enum: ['owner', 'manager', 'receptionist'],
                        example: 'manager',
                        description: 'Função do usuário no hotel'
                    ),
                    new OA\Property(
                        property: 'is_active',
                        type: 'boolean',
                        nullable: true,
                        example: true,
                        description: 'Indica se o usuário pode acessar o sistema'
                    )
                ],
                example: [
                    'name' => 'Maria Souza',
                    'email' => 'maria.souza@hotel.com',
                    'password' => 'senha-segura-123',
                    'role' => 'manager',
                    'is_active' => true,
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Usuário criado com sucesso',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/HotelUser'
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
                description: 'Sem permissão para criar usuários neste hotel'
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
        $hotel = Hotel::findOrFail($hotelId);
        $creator = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string', 'in:'.User::ROLE_OWNER.','.User::ROLE_MANAGER.','.User::ROLE_RECEPTIONIST],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $user = $this->HotelUserService->createUserInHotel($validated, $hotel, $creator);

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Get(
        path: '/api/hotels/{hotelId}/users/{userId}',
        tags: ['Usuários'],
        summary: 'Busca um usuário do hotel',
        description: 'Retorna um usuário que pertence ao hotel informado.',
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
                name: 'userId',
                in: 'path',
                required: true,
                description: 'ID do usuário',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Usuário encontrado',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/HotelUser'
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
                description: 'Sem permissão para acessar o usuário'
            ),
            new OA\Response(
                response: 404,
                description: 'Hotel ou usuário não encontrado'
            )
        ]
    )]
    public function show(int $hotelId, int $userId): JsonResponse
    {
        $hotel = Hotel::findOrFail($hotelId);
        $user = $this->HotelUserService->findUserByHotel($hotel, $userId);

        return (new UserResource($user))->toResponse(request());
    }

    #[OA\Put(
        path: '/api/hotels/{hotelId}/users/{userId}',
        tags: ['Usuários'],
        summary: 'Atualiza um usuário do hotel',
        description: 'Atualiza os dados informados para um usuário pertencente ao hotel.',
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
                name: 'userId',
                in: 'path',
                required: true,
                description: 'ID do usuário',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        maxLength: 255,
                        example: 'Maria Souza'
                    ),
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        example: 'maria.souza@hotel.com'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        format: 'password',
                        minLength: 8,
                        example: 'nova-senha-segura'
                    ),
                    new OA\Property(
                        property: 'role',
                        type: 'string',
                        enum: ['owner', 'manager', 'receptionist'],
                        example: 'receptionist'
                    ),
                    new OA\Property(
                        property: 'is_active',
                        type: 'boolean',
                        example: true
                    )
                ],
                example: [
                    'name' => 'Maria Souza',
                    'role' => 'receptionist',
                    'is_active' => true,
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Usuário atualizado com sucesso',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/HotelUser'
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
                description: 'Sem permissão para atualizar o usuário'
            ),
            new OA\Response(
                response: 404,
                description: 'Hotel ou usuário não encontrado'
            ),
            new OA\Response(
                response: 422,
                description: 'Dados inválidos'
            )
        ]
    )]
    #[OA\Patch(
        path: '/api/hotels/{hotelId}/users/{userId}',
        tags: ['Usuários'],
        summary: 'Atualiza parcialmente um usuário do hotel',
        description: 'Atualiza somente os campos enviados para um usuário pertencente ao hotel.',
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
                name: 'userId',
                in: 'path',
                required: true,
                description: 'ID do usuário',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Maria Souza'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'maria.souza@hotel.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8),
                    new OA\Property(
                        property: 'role',
                        type: 'string',
                        enum: ['owner', 'manager', 'receptionist'],
                        example: 'manager'
                    ),
                    new OA\Property(property: 'is_active', type: 'boolean', example: true)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Usuário atualizado com sucesso',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/HotelUser'
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Não autenticado'),
            new OA\Response(response: 403, description: 'Sem permissão para atualizar o usuário'),
            new OA\Response(response: 404, description: 'Hotel ou usuário não encontrado'),
            new OA\Response(response: 422, description: 'Dados inválidos')
        ]
    )]
    public function update(Request $request, int $hotelId, int $userId): JsonResponse
    {
        $hotel = Hotel::findOrFail($hotelId);
        $updater = $request->user();

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email'],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['nullable', 'string', 'in:'.User::ROLE_OWNER.','.User::ROLE_MANAGER.','.User::ROLE_RECEPTIONIST],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $user = $this->HotelUserService->updateUserInHotel($validated, $hotel, $userId, $updater);

        return (new UserResource($user))->toResponse(request());
    }

    #[OA\Delete(
        path: '/api/hotels/{hotelId}/users/{userId}',
        tags: ['Usuários'],
        summary: 'Remove um usuário do hotel',
        description: 'Remove um usuário pertencente ao hotel informado.',
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
                name: 'userId',
                in: 'path',
                required: true,
                description: 'ID do usuário',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Usuário removido com sucesso'
            ),
            new OA\Response(
                response: 401,
                description: 'Não autenticado'
            ),
            new OA\Response(
                response: 403,
                description: 'Sem permissão para remover o usuário'
            ),
            new OA\Response(
                response: 404,
                description: 'Hotel ou usuário não encontrado'
            )
        ]
    )]
    public function destroy(Request $request, int $hotelId, int $userId): Response
    {
        $hotel = Hotel::findOrFail($hotelId);
        $deleter = $request->user();

        $this->HotelUserService->deleteUserFromHotel($hotel, $userId, $deleter);

        return response()->noContent();
    }
}
