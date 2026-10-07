<?php

namespace App\Services;

use App\Models\User;
use App\Models\Hotel;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Collection;

class UserService
{
    public function canUserManageHotelUsers(User $user, Hotel $hotel): bool
    {
        if ($user->hotel_id !== $hotel->id) {
            return false;
        }

        return $user->canManageUsers();
    }

    public function listUsersByHotel(Hotel $hotel): Collection
    {
        return User::where('hotel_id', $hotel->id)->get();
    }

    public function findUserByHotel(Hotel $hotel, int $userId): User
    {
        return User::where('hotel_id', $hotel->id)
            ->findOrFail($userId);
    }

    public function createUserInHotel(array $data, Hotel $hotel, User $creator): User
    {
        if (!$this->canUserManageHotelUsers($creator, $hotel)) {
            throw new \Illuminate\Auth\Access\AuthorizationException(
                'Sem permissão para criar usuários neste hotel.'
            );
        }

        $validated = validator($data, [
            'name' => ['required', 'string', 'max:255'],

            'email' => ['required', 'email'],

            'password' => ['required', 'string', 'min:8'],

            'role' => ['required', Rule::in([
                User::ROLE_OWNER,
                User::ROLE_MANAGER,
                User::ROLE_RECEPTIONIST,
            ])],

            'is_active' => ['nullable', 'boolean'],
        ])->validate();

        $existingUser = User::where('hotel_id', $hotel->id)
            ->where('email', $validated['email'])
            ->first();

        if ($existingUser) {
            throw new \Illuminate\Validation\ValidationException(
                'Já existe um usuário com este email neste hotel.'
            );
        }

        return User::create([
            'hotel_id' => $hotel->id,

            'name' => $validated['name'],

            'email' => $validated['email'],

            'password' => Hash::make($validated['password']),

            'role' => $validated['role'],

            'is_active' => $validated['is_active'] ?? true,
        ]);
    }

    public function updateUserInHotel(array $data, Hotel $hotel, int $userId, User $updater): User
    {
        $user = $this->findUserByHotel($hotel, $userId);

        if (!$this->canUserManageHotelUsers($updater, $hotel)) {
            throw new \Illuminate\Auth\Access\AuthorizationException(
                'Sem permissão para atualizar usuários neste hotel.'
            );
        }

        $validated = validator($data, [
            'name' => ['nullable', 'string', 'max:255'],

            'email' => ['nullable', 'email'],

            'password' => ['nullable', 'string', 'min:8'],

            'role' => ['nullable', Rule::in([
                User::ROLE_OWNER,
                User::ROLE_MANAGER,
                User::ROLE_RECEPTIONIST,
            ])],

            'is_active' => ['nullable', 'boolean'],
        ])->validate();

        // Verifica se email já existe no hotel (ignorando o próprio usuário)
        if (isset($validated['email'])) {
            $existingUser = User::where('hotel_id', $hotel->id)
                ->where('email', $validated['email'])
                ->where('id', '!=', $userId)
                ->first();

            if ($existingUser) {
                throw new \Illuminate\Validation\ValidationException(
                    'Já existe um usuário com este email neste hotel.'
                );
            }
        }

        if (isset($validated['name'])) {
            $user->name = $validated['name'];
        }

        if (isset($validated['email'])) {
            $user->email = $validated['email'];
        }

        if (isset($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        if (isset($validated['role'])) {
            $user->role = $validated['role'];
        }

        if (isset($validated['is_active'])) {
            $user->is_active = $validated['is_active'];
        }

        $user->save();

        return $user;
    }

    public function deleteUserFromHotel(Hotel $hotel, int $userId, User $deleter): void
    {
        $user = $this->findUserByHotel($hotel, $userId);

        if (!$this->canUserManageHotelUsers($deleter, $hotel)) {
            throw new \Illuminate\Auth\Access\AuthorizationException(
                'Sem permissão para remover usuários neste hotel.'
            );
        }

        $user->delete();
    }
}
