<?php

namespace App\Services;

use App\Models\Room;
use Illuminate\Pagination\LengthAwarePaginator;

 class RoomService {
    public function paginateRooms(): LengthAwarePaginator
    {
        $rooms = Room::query()
            ->with('hotel')
            ->paginate(15);

        return $rooms;
    }

    public function getRoom(Room $room): Room
    {
        $room->load('hotel');

        return $room;
    }
     public function createRoom(array $data): Room
     {
         return Room::create($data);
     }

    public function updateRoom(Room $room, array $data): Room
    {
        $room->update($data);

        return $room;
    }

    public function deleteRoom(Room $room): void
    {
        $room->delete();
    }
}
