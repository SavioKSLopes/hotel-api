<?php

namespace Database\Seeders;

use App\Models\Hotel;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $hotels = Hotel::all();

        if ($hotels->isEmpty()) {
            return;
        }

        foreach ($hotels as $hotel) {
            $existingOwner = User::where('hotel_id', $hotel->id)
                ->where('role', User::ROLE_OWNER)
                ->first();

            if ($existingOwner) {
                continue;
            }

            User::create([
                'hotel_id' => $hotel->id,
                'name' => 'Admin '.$hotel->name,
                'email' => 'admin@hotel'.$hotel->id.'.com',
                'password' => Hash::make('password'),
                'role' => User::ROLE_OWNER,
                'is_active' => true,
            ]);
        }
    }
}
