<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $rooms = [
            ['name' => 'H.4.1', 'capacity' => 60],
            ['name' => 'H.4.5', 'capacity' => 60],
            ['name' => 'H.4.6', 'capacity' => 60],
            ['name' => 'H.4.7', 'capacity' => 60],
            ['name' => 'H.4.8', 'capacity' => 60],
            ['name' => 'Hall H', 'capacity' => 100],
            ['name' => 'F.1.2', 'capacity' => 50],
            ['name' => 'F.1.3', 'capacity' => 50],
            ['name' => 'F.1.4', 'capacity' => 50],
            ['name' => 'F.1.5', 'capacity' => 50],
            ['name' => 'F.2.3', 'capacity' => 50],
            ['name' => 'F.2.4', 'capacity' => 100],
            ['name' => 'J.3.2', 'capacity' => 50],
            ['name' => 'J.3.3', 'capacity' => 50],
        ];

        foreach ($rooms as $room) {
            Room::updateOrCreate(
                ['name' => $room['name']],
                ['capacity' => $room['capacity']],
            );
        }
    }
}
