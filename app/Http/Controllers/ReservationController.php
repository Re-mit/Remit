<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    /**
     * Display reservation form and list
     */
    /**
     * Display reservation form (예약하기)
     */
    public function index()
    {
        $reservations = Reservation::with(['room', 'users'])
            ->where('status', 'confirmed')
            ->where('start_at', '>=', now())
            ->orderBy('start_at')
            ->get();

        return view('reservation.index', compact('reservations'));
    }

    /**
     * Store a new reservation
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'start_at' => 'required|date|after:now',
            'end_at' => 'required|date|after:start_at',
            'participants' => 'nullable|string',
        ]);

        // 시간 검증
        $startAt = new \DateTime($validated['start_at']);
        $endAt = new \DateTime($validated['end_at']);
        $duration = ($endAt->getTimestamp() - $startAt->getTimestamp()) / 3600; // 시간 단위

        if ($duration > 4) {
            return back()->withErrors(['end_at' => '예약은 최대 4시간까지만 가능합니다.']);
        }

        // 1주일 이내 제한 검증
        $oneWeekLater = now()->addWeek();
        if ($startAt > $oneWeekLater) {
            return back()->withErrors(['start_at' => '예약은 1주일 이내로만 가능합니다.']);
        }

        try {
            DB::beginTransaction();

            // 기본 방 (622호) 가져오기
            $room = Room::where('name', '622호')->firstOrFail();

            // 중복 예약 확인 (동시성 제어)
            $overlapping = Reservation::where('room_id', $room->id)
                ->where('status', 'confirmed')
                ->where(function ($query) use ($validated) {
                    $query->where(function ($q) use ($validated) {
                        $q->where('start_at', '<', $validated['end_at'])
                          ->where('end_at', '>', $validated['start_at']);
                    });
                })
                ->lockForUpdate()
                ->exists();

            if ($overlapping) {
                DB::rollBack();
                return back()->withErrors(['start_at' => '이미 그 시간대에 예약이 있습니다.']);
            }

            // 예약 생성
            $reservation = Reservation::create([
                'room_id' => $room->id,
                'start_at' => $validated['start_at'],
                'end_at' => $validated['end_at'],
                'key_code' => $this->generateKeyCode(),
                'status' => 'confirmed',
            ]);

            // 임시 사용자 생성 (Google OAuth 전까지)
            $user = User::firstOrCreate(
                ['email' => 'test@example.com'],
                [
                    'name' => '테스트 사용자',
                    'password' => null,
                    'role' => 'user',
                ]
            );

            // 예약-사용자 연결 (대표자)
            $reservation->users()->attach($user->id, ['is_representative' => true]);

            // 참여자 처리 (추후 구현)
            // if ($request->participants) { ... }

            DB::commit();

            return back()->with('success', '예약이 완료되었습니다! 열쇠함 비밀번호: ' . $reservation->key_code);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => '예약 중 오류가 발생했습니다: ' . $e->getMessage()]);
        }
    }

    /**
     * Show my reservations
     */
    public function my()
    {
        // 임시 사용자로 조회 (Google OAuth 전까지)
        $user = User::where('email', 'test@example.com')->first();
        
        $reservations = collect();
        if ($user) {
            $reservations = $user->reservations()
                ->with(['room', 'users'])
                ->orderBy('start_at', 'desc')
                ->get();
        }

        return view('reservation.my', compact('reservations'));
    }

    /**
     * Cancel reservation
     */
    public function destroy($id)
    {
        $reservation = Reservation::findOrFail($id);

        // 권한 체크 (임시: 모든 예약 취소 가능)
        // TODO: Google OAuth 구현 후 대표자/관리자만 취소 가능하도록 변경

        if ($reservation->status !== 'confirmed') {
            return back()->withErrors(['error' => '이미 취소된 예약입니다.']);
        }

        $reservation->update(['status' => 'cancelled']);

        return back()->with('success', '예약이 취소되었습니다.');
    }

    /**
     * Generate random 4-digit key code
     */
    private function generateKeyCode()
    {
        return str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    }
}
