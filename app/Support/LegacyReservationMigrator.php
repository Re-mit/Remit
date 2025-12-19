<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class LegacyReservationMigrator
{
    /**
     * 과거에 임시 계정(test@example.com)으로 저장된 예약을
     * 현재 로그인 사용자로 자동 이관한다.
     *
     * - 로그인 유저가 이미 예약이 있으면(=운영 중 계정) 이관하지 않음
     * - 유니크 제약(reservation_id, user_id)을 피하기 위해 reservation 단위로 안전 처리
     */
    public static function migrateFromTestUserIfNeeded(User $user): void
    {
        $testUser = User::where('email', 'test@example.com')->first();
        if (!$testUser) {
            return;
        }

        if ($testUser->id === $user->id) {
            return;
        }

        // 이미 본인 계정으로 예약이 존재하면 (운영/실사용) 이관하지 않음
        $hasAnyReservation = DB::table('reservation_users')
            ->where('user_id', $user->id)
            ->exists();

        if ($hasAnyReservation) {
            return;
        }

        $legacyReservationIds = DB::table('reservation_users')
            ->where('user_id', $testUser->id)
            ->pluck('reservation_id');

        if ($legacyReservationIds->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($legacyReservationIds, $testUser, $user) {
            foreach ($legacyReservationIds as $reservationId) {
                $alreadyLinked = DB::table('reservation_users')
                    ->where('reservation_id', $reservationId)
                    ->where('user_id', $user->id)
                    ->exists();

                if ($alreadyLinked) {
                    // 이미 연결되어 있으면 test 유저 연결만 제거
                    DB::table('reservation_users')
                        ->where('reservation_id', $reservationId)
                        ->where('user_id', $testUser->id)
                        ->delete();
                    continue;
                }

                // test 유저 연결을 로그인 유저로 user_id만 교체 (대표자 플래그 유지)
                DB::table('reservation_users')
                    ->where('reservation_id', $reservationId)
                    ->where('user_id', $testUser->id)
                    ->update([
                        'user_id' => $user->id,
                        'updated_at' => now(),
                    ]);
            }
        });
    }
}



