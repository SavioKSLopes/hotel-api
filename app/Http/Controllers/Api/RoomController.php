<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoomRequest;
use App\Http\Requests\UpdateRoomRequest;
use App\Http\Resources\RoomResource;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class RoomController extends Controller
{
    #[OA\Get(
        path: '/api/rooms',
        tags: ['Quartos'],
        summary: 'Lista os quartos',
        description: 'Retorna uma lista paginada de todos os quartos cadastrados, incluindo o hotel associado.',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Quartos retornados com sucesso',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Room')
                        ),
                        new OA\Property(property: 'links', type: 'object'),
                        new OA\Property(property: 'meta', type: 'object')
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Não autenticado'
            )
        ]
    )]
    public function index(): AnonymousResourceCollection
    {
        $rooms = Room::query()
            ->with('hotel')
            ->paginate(15);

        return RoomResource::collection($rooms);
    }

    #[OA\Post(
        path: '/api/rooms',
        tags: ['Quartos'],
        summary: 'Cria um quarto',
        description: 'Cria um novo quarto associado a um hotel.',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['hotel_id', 'external_id', 'name'],
                properties: [
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
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Quarto criado com sucesso',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/Room'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Não autenticado'
            ),
            new OA\Response(
                response: 422,
                description: 'Dados inválidos',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'The hotel id field is required.'
                        ),
                        new OA\Property(
                            property: 'errors',
                            type: 'object',
                            example: [
                                'hotel_id' => [
                                    'The hotel id field is required.'
                                ]
                            ]
                        )
                    ]
                )
            )
        ]
    )]
    public function store(StoreRoomRequest $request): JsonResponse
    {
        $data = $request->validated();

        $room = Room::create($data);

        return (new RoomResource($room))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Get(
        path: '/api/rooms/{room}',
        tags: ['Quartos'],
        summary: 'Busca um quarto',
        description: 'Retorna os dados de um quarto pelo seu identificador.',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'room',
                in: 'path',
                required: true,
                description: 'ID do quarto',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Quarto encontrado',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/Room'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Não autenticado'
            ),
            new OA\Response(
                response: 404,
                description: 'Quarto não encontrado'
            )
        ]
    )]
    public function show(Room $room): RoomResource
    {
        return new RoomResource($room);
    }

    #[OA\Put(
        path: '/api/rooms/{room}',
        tags: ['Quartos'],
        summary: 'Atualiza um quarto',
        description: 'Atualiza todos os campos de um quarto existente.',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'room',
                in: 'path',
                required: true,
                description: 'ID do quarto',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['hotel_id', 'external_id', 'name'],
                properties: [
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
                        example: 'Quarto Standard Premium 101'
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Quarto atualizado com sucesso',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/Room'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Não autenticado'
            ),
            new OA\Response(
                response: 404,
                description: 'Quarto não encontrado'
            ),
            new OA\Response(
                response: 422,
                description: 'Dados inválidos'
            )
        ]
    )]
    #[OA\Patch(
        path: '/api/rooms/{room}',
        tags: ['Quartos'],
        summary: 'Atualiza parcialmente um quarto',
        description: 'Atualiza somente os campos informados para um quarto existente.',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'room',
                in: 'path',
                required: true,
                description: 'ID do quarto',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
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
                        example: 'Quarto Standard Premium 101'
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Quarto atualizado com sucesso',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/Room'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Não autenticado'
            ),
            new OA\Response(
                response: 404,
                description: 'Quarto não encontrado'
            ),
            new OA\Response(
                response: 422,
                description: 'Dados inválidos'
            )
        ]
    )]
    public function update(UpdateRoomRequest $request, Room $room): RoomResource
    {
        $room->update($request->validated());

        return new RoomResource($room);
    }

    #[OA\Delete(
        path: '/api/rooms/{room}',
        tags: ['Quartos'],
        summary: 'Remove um quarto',
        description: 'Exclui um quarto pelo seu identificador.',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'room',
                in: 'path',
                required: true,
                description: 'ID do quarto',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Quarto removido com sucesso'
            ),
            new OA\Response(
                response: 401,
                description: 'Não autenticado'
            ),
            new OA\Response(
                response: 404,
                description: 'Quarto não encontrado'
            )
        ]
    )]
    public function destroy(Room $room): JsonResponse
    {
        $room->delete();

        return response()->json(null, 204);
    }
}
