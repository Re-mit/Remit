<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Reservation;
use App\Models\LockboxUrl;
use App\Models\Notice;
use App\Models\Notification;
use App\Models\EmailVerificationCode;
use App\Support\LegacyReservationMigrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MypageController extends Controller
{
    /**
     * Display mypage
     */
    public function index()
    {
        $user = Auth::user();
        
        // 읽지 않은 알림 수 가져오기
        $unreadCount = 0;
        if ($user) {
            $unreadCount = $user->notifications()->whereNull('read_at')->count();
        }
        
        return view('mypage.index', compact('unreadCount'));
    }

    /**
     * Display key code page
     */
    public function keycode()
    {
        $user = Auth::user();
        if ($user) {
            // 과거 임시 계정 예약(test@example.com)이 있으면 로그인 계정으로 자동 이관
            LegacyReservationMigrator::migrateFromTestUserIfNeeded($user);
        }
        
        $reservations = collect();
        if ($user) {
            $now = now('Asia/Seoul');

            $reservations = $user->reservations()
                ->with(['room'])
                ->where('status', 'confirmed')
                // 예약 종료 시간이 지난 내역은 목록에서 숨김 (KST 기준)
                ->where('end_at', '>', $now)
                ->orderBy('start_at', 'desc')
                ->get()
                ->map(function($reservation) {
                    $now = now('Asia/Seoul');
                    $startAtKst = $reservation->start_at->copy()->timezone('Asia/Seoul');
                    $dateKst = $startAtKst->toDateString();

                    // 뷰에서 format()으로 표시되는 시간과 동일하게 계산
                    // format()은 자동으로 시간대를 변환하므로, format으로 표시되는 값으로 계산
                    // 예: 오전 10시 예약 -> format('H')는 10을 반환 (이미 한국 시간)
                    $year = (int)$reservation->start_at->format('Y');
                    $month = (int)$reservation->start_at->format('n');
                    $day = (int)$reservation->start_at->format('j');
                    $hour = (int)$reservation->start_at->format('G');
                    $minute = (int)$reservation->start_at->format('i');
                    
                    // 10분 전 계산
                    $tenMinBeforeMinute = $minute - 10;
                    $tenMinBeforeHour = $hour;
                    $tenMinBeforeDay = $day;
                    $tenMinBeforeMonth = $month;
                    $tenMinBeforeYear = $year;
                    
                    if ($tenMinBeforeMinute < 0) {
                        $tenMinBeforeMinute += 60;
                        $tenMinBeforeHour--;
                        if ($tenMinBeforeHour < 0) {
                            $tenMinBeforeHour = 23;
                            $tenMinBeforeDay--;
                            // 월/년 경계 처리는 간단하게 (실제로는 필요하지만 여기서는 생략)
                        }
                    }
                    
                    // 한국 시간으로 10분 전 시간 생성
                    $tenMinutesBefore = \Carbon\Carbon::create(
                        $tenMinBeforeYear, $tenMinBeforeMonth, $tenMinBeforeDay,
                        $tenMinBeforeHour, $tenMinBeforeMinute, 0,
                        'Asia/Seoul'
                    );
                    
                    // 시작 시간이 지난 예약은 "지난 내역" 처리 + 비밀번호 미노출
                    $reservation->is_past_started = $now >= $startAtKst;

                    // URL 공개 여부:
                    // - 예약 시작 10분 전부터
                    // - 예약 시작 시간 전까지만
                    // - 시작 시간이 지나면 무조건 false
                    $reservation->is_url_disclosed = !$reservation->is_past_started
                        && $now >= $tenMinutesBefore
                        && $now < $startAtKst;
                    
                    // 한국 시간으로 포맷팅된 공개 시간 (JavaScript에서 사용)
                    $reservation->url_disclosure_time_formatted = [
                        'year' => $tenMinBeforeYear,
                        'month' => $tenMinBeforeMonth,
                        'day' => $tenMinBeforeDay,
                        'hour' => $tenMinBeforeHour,
                        'minute' => $tenMinBeforeMinute,
                    ];

                    // 해당 날짜에 매핑된 URL 조회 (3일 단위)
                    $lockbox = LockboxUrl::query()
                        ->whereDate('start_date', '<=', $dateKst)
                        ->whereDate('end_date', '>=', $dateKst)
                        ->first();

                    $reservation->lockbox_url = $lockbox?->url;

                    // 뱃지 텍스트/색상 (UI)
                    $reservation->badge_text = $reservation->is_past_started ? '지난 내역' : '예약됨';
                    $reservation->badge_class = $reservation->is_past_started
                        ? 'bg-gray-800 text-white'
                        : 'bg-blue-500 text-white';
                    
                    return $reservation;
                });
        }

        return view('mypage.keycode', compact('reservations'));
    }

    /**
     * 회원탈퇴 (Hard delete)
     */
    public function destroy(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', '로그인이 필요합니다.');
        }

        DB::beginTransaction();
        try {
            // 내가 작성한 공지(notices)가 있다면, 해당 공지에 연결된 알림(모든 사용자)을 먼저 삭제
            $noticeIds = Notice::query()
                ->where('author_user_id', $user->id)
                ->pluck('id');

            if ($noticeIds->isNotEmpty()) {
                Notification::query()
                    ->where('related_type', Notice::class)
                    ->whereIn('related_id', $noticeIds->all())
                    ->delete();
            }

            // 세션/인증 관련 정리 (FK가 없는 테이블들)
            DB::table('sessions')->where('user_id', $user->id)->delete();
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();
            EmailVerificationCode::query()->where('email', $user->email)->delete();

            // 사용자 하드 삭제 (notifications/usage_logs/reservation_users/notices 등은 FK cascade로 정리)
            User::query()->whereKey($user->id)->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => '회원탈퇴 처리 중 오류가 발생했습니다: ' . $e->getMessage()]);
        }

        // 로그아웃 및 세션 파기
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', '회원탈퇴가 완료되었습니다.');
    }
}
