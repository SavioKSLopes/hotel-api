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

class HotelUserController extends Controller
{
    public function __construct(
        private HotelUserService $HotelUserService,
    ) {}

    public function index(int $hotelId): AnonymousResourceCollection
    {
        $hotel = Hotel::findOrFail($hotelId);
        $users = $this->HotelUserService->listUsersByHotel($hotel);

        return UserResource::collection($users);
    }

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

        return (new UserResource($user))->toResponse(request());
    }

    public function show(int $hotelId, int $userId): JsonResponse
    {
        $hotel = Hotel::findOrFail($hotelId);
        $user = $this->HotelUserService->findUserByHotel($hotel, $userId);

        return (new UserResource($user))->toResponse(request());
    }

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

    public function destroy(Request $request, int $hotelId, int $userId): Response
    {
        $hotel = Hotel::findOrFail($hotelId);
        $deleter = $request->user();

        $this->HotelUserService->deleteUserFromHotel($hotel, $userId, $deleter);

        return response()->noContent();
    }
}
