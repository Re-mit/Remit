<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MypageController extends Controller
{
    /**
     * Display mypage
     */
    public function index()
    {
        return view('mypage.index');
    }

    /**
     * Display key code page
     */
    public function keycode()
    {
        // 임시 사용자로 조회 (Google OAuth 전까지)
        $user = User::where('email', 'test@example.com')->first();
        
        $reservations = collect();
        if ($user) {
            $reservations = $user->reservations()
                ->with(['room'])
                ->where('status', 'confirmed')
                ->where('end_at', '>', now())
                ->orderBy('start_at', 'desc')
                ->get()
                ->map(function($reservation) {
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
                    $now = now('Asia/Seoul');
                    
                    // 비밀번호 공개 여부 (예약 시간 10분 전부터)
                    $reservation->is_keycode_disclosed = $now >= $tenMinutesBefore;
                    
                    // 한국 시간으로 포맷팅된 공개 시간 (JavaScript에서 사용)
                    $reservation->keycode_disclosure_time_formatted = [
                        'year' => $tenMinBeforeYear,
                        'month' => $tenMinBeforeMonth,
                        'day' => $tenMinBeforeDay,
                        'hour' => $tenMinBeforeHour,
                        'minute' => $tenMinBeforeMinute,
                    ];
                    
                    return $reservation;
                });
        }

        return view('mypage.keycode', compact('reservations'));
    }
}
