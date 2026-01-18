<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\Reservation;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class PurgeOldReservations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * --dry-run : 실제 삭제 없이 대상 개수만 출력
     * --months  : 보관 개월 수(기본: config('reservation.retention_months', 1))
     */
    protected $signature = 'reservations:purge-old {--dry-run : Do not delete, only show counts} {--months= : Retention months override}';

    /**
     * The console command description.
     */
    protected $description = '예약 내역을 최근 N개월만 유지하고, 초과분(완료/취소)을 DB에서 삭제합니다.';

    public function handle(): int
    {
        $tz = 'Asia/Seoul';

        $monthsOpt = $this->option('months');
        $months = is_null($monthsOpt) || $monthsOpt === ''
            ? (int) config('reservation.retention_months', 1)
            : (int) $monthsOpt;

        if ($months < 1) {
            $this->error('months는 1 이상이어야 합니다.');
            return self::FAILURE;
        }

        $cutoff = Carbon::now($tz)->subMonthsNoOverflow($months)->startOfDay();

        // 삭제 기준:
        // - confirmed: end_at < cutoff (완전히 끝난 지 N개월 초과)
        // - cancelled: cancelled_at < cutoff (취소된 지 N개월 초과) [cancelled_at이 없으면 end_at 기준 fallback]
        $baseQuery = Reservation::query()->where(function ($q) use ($cutoff) {
            $q->where(function ($qq) use ($cutoff) {
                $qq->where('status', 'confirmed')
                    ->where('end_at', '<', $cutoff);
            })->orWhere(function ($qq) use ($cutoff) {
                $qq->where('status', 'cancelled')
                    ->where(function ($qqq) use ($cutoff) {
                        $qqq->whereNotNull('cancelled_at')->where('cancelled_at', '<', $cutoff)
                            ->orWhere(function ($qqqq) use ($cutoff) {
                                $qqqq->whereNull('cancelled_at')->where('end_at', '<', $cutoff);
                            });
                    });
            });
        });

        $targetCount = (clone $baseQuery)->count();
        $this->info("cutoff: {$cutoff->toDateTimeString()} ({$tz})");
        $this->info("대상 예약 수: {$targetCount}");

        if ($this->option('dry-run')) {
            $this->comment('dry-run 모드: 삭제하지 않았습니다.');
            return self::SUCCESS;
        }

        $deletedReservations = 0;
        $deletedNotifications = 0;

        (clone $baseQuery)
            ->select(['id'])
            ->orderBy('id')
            ->chunkById(500, function ($rows) use (&$deletedReservations, &$deletedNotifications) {
                $ids = $rows->pluck('id')->filter()->values();
                if ($ids->isEmpty()) {
                    return;
                }

                // 예약 관련 알림(related_type/reservation_id)도 같이 삭제해서 dangling 참조 방지
                $deletedNotifications += Notification::query()
                    ->where('related_type', Reservation::class)
                    ->whereIn('related_id', $ids)
                    ->delete();

                $deletedReservations += Reservation::query()
                    ->whereIn('id', $ids)
                    ->delete();
            });

        $this->info("삭제 완료 - reservations: {$deletedReservations}, notifications: {$deletedNotifications}");

        return self::SUCCESS;
    }
}



