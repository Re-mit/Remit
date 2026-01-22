<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\Seat;
use Illuminate\Database\Seeder;

class SeatSeeder extends Seeder
{
    public function run(): void
    {
        $room = Room::whereIn('name', ['가천관 622호', '622호'])->first();
        if (!$room) {
            return;
        }

        $count = (int) config('reservation.default_seat_count', 6);
        if ($count < 1) {
            $count = 1;
        }
        $max = (int) config('reservation.max_seat_count', 6);
        if ($max > 0) {
            $count = min($count, $max);
        }

        for ($i = 1; $i <= $count; $i++) {
            Seat::updateOrCreate(
                [
                    'room_id' => $room->id,
                    'label' => "{$i}번",
                ],
                [
                    'is_active' => true,
                ]
            );
        }

        // count 초과 좌석은 비활성화
        Seat::where('room_id', $room->id)
            ->where('is_active', true)
            ->where(function ($q) use ($count) {
                for ($i = $count + 1; $i <= 50; $i++) {
                    $q->orWhere('label', "{$i}번");
                }
            })
            ->update(['is_active' => false]);
    }
}


