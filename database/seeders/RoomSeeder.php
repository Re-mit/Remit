<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Room::updateOrCreate(
            ['name' => '622호'],
            [
                'description' => '학과 공용 스터디룸',
            ]
        );
    }
}
