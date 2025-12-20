<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use App\Support\LegacyReservationMigrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        // 읽지 않은 알림 수 가져오기
        $user = Auth::user();
        $unreadCount = 0;
        if ($user) {
            $unreadCount = $user->notifications()->whereNull('read_at')->count();
        }

        return view('reservation.index', compact('reservations', 'unreadCount'));
    }

    /**
     * Store a new reservation
     */
    public function store(Request $request)
    {
        // 신규: segments(JSON)로 여러 구간 예약 지원
        if ($request->filled('segments')) {
            $segmentsRaw = $request->input('segments');
            $segments = json_decode($segmentsRaw, true);
            if (!is_array($segments) || empty($segments)) {
                return back()->withErrors(['start_at' => '예약 시간이 올바르지 않습니다.']);
            }

            // 간단 검증 (각 구간의 시작/종료)
            foreach ($segments as $i => $seg) {
                if (!is_array($seg) || empty($seg['start_at']) || empty($seg['end_at'])) {
                    return back()->withErrors(['start_at' => '예약 시간이 올바르지 않습니다.']);
                }
                if (!strtotime($seg['start_at']) || !strtotime($seg['end_at'])) {
                    return back()->withErrors(['start_at' => '예약 시간이 올바르지 않습니다.']);
                }
            }
        } else {
            // 기존 단일 구간 예약(호환)
            $validated = $request->validate([
                'start_at' => 'required|date|after:now',
                'end_at' => 'required|date|after:start_at',
                'participants' => 'nullable|string',
            ]);
            $segments = [[
                'start_at' => $validated['start_at'],
                'end_at' => $validated['end_at'],
            ]];
        }

        // 각 구간 파싱 및 정렬
        $parsed = collect($segments)->map(function ($seg) {
            $startAt = new \DateTime($seg['start_at']);
            $endAt = new \DateTime($seg['end_at']);
            return [
                'start_at' => $startAt,
                'end_at' => $endAt,
                'duration' => ($endAt->getTimestamp() - $startAt->getTimestamp()) / 3600,
            ];
        })->sortBy(fn ($x) => $x['start_at']->getTimestamp())->values();

        // 기본 검증: 각 구간 1시간 단위, 최대 4시간/일(총합), 1주일 이내
        $firstDate = $parsed->first()['start_at']->format('Y-m-d');
        $oneWeekLater = now()->addWeek();

        $newTotalHours = 0;
        foreach ($parsed as $seg) {
            /** @var \DateTime $startAt */
            $startAt = $seg['start_at'];
            /** @var \DateTime $endAt */
            $endAt = $seg['end_at'];
            $duration = $seg['duration'];

            if ($duration <= 0) {
                return back()->withErrors(['start_at' => '예약 시간이 올바르지 않습니다.']);
            }

            // 각 예약은 4시간 초과 불가
            if ($duration > 4) {
                return back()->withErrors(['end_at' => '예약은 최대 4시간까지만 가능합니다.']);
            }

            // 시작은 현재 이후
            if ($startAt <= new \DateTime()) {
                return back()->withErrors(['start_at' => '예약 시작 시간은 현재 이후여야 합니다.']);
            }

            // 1주일 이내
            if ($startAt > $oneWeekLater) {
                return back()->withErrors(['start_at' => '예약은 1주일 이내로만 가능합니다.']);
            }

            // 같은 날짜(분리 구간은 같은 날짜만 허용)
            if ($startAt->format('Y-m-d') !== $firstDate || $endAt->format('Y-m-d') !== $firstDate) {
                return back()->withErrors(['start_at' => '서로 다른 날짜로 나뉜 예약은 지원하지 않습니다.']);
            }

            $newTotalHours += $duration;
        }

        if ($newTotalHours > 4) {
            return back()->withErrors(['start_at' => '하루에 최대 4시간까지만 예약할 수 있습니다.']);
        }

        try {
            DB::beginTransaction();

            // 로그인 사용자 기준으로 예약 저장
            $user = Auth::user();
            if (!$user) {
                DB::rollBack();
                return redirect()->route('login')->with('error', '로그인이 필요합니다.');
            }

            // 과거 임시 계정 예약(test@example.com)이 있으면 로그인 계정으로 자동 이관
            LegacyReservationMigrator::migrateFromTestUserIfNeeded($user);

            // 해당 날짜에 사용자가 이미 예약한 예약들의 총 시간 계산
            $reservationDate = $firstDate;
            $existingReservations = $user->reservations()
                ->where('status', 'confirmed')
                ->whereDate('start_at', $reservationDate)
                ->get();

            $totalHours = 0;
            foreach ($existingReservations as $existingReservation) {
                $existingStart = new \DateTime($existingReservation->start_at);
                $existingEnd = new \DateTime($existingReservation->end_at);
                $existingDuration = ($existingEnd->getTimestamp() - $existingStart->getTimestamp()) / 3600;
                $totalHours += $existingDuration;
            }

            // 새로 예약하려는 시간 추가(여러 구간 합산)
            $totalHours += $newTotalHours;

            if ($totalHours > 4) {
                DB::rollBack();
                return back()->withErrors(['start_at' => '하루에 최대 4시간까지만 예약할 수 있습니다. (현재 예약: ' . ($totalHours - $newTotalHours) . '시간)']);
            }

            // 기본 방 (가천관 622호) 가져오기 (레거시 데이터 '622호'도 허용)
            $room = Room::whereIn('name', ['가천관 622호', '622호'])->firstOrFail();

            $createdIds = [];

            foreach ($parsed as $seg) {
                $startStr = $seg['start_at']->format('Y-m-d\TH:i:s');
                $endStr = $seg['end_at']->format('Y-m-d\TH:i:s');

                // 중복 예약 확인 (동시성 제어)
                $overlapping = Reservation::where('room_id', $room->id)
                    ->where('status', 'confirmed')
                    ->where('start_at', '<', $endStr)
                    ->where('end_at', '>', $startStr)
                    ->lockForUpdate()
                    ->exists();

                if ($overlapping) {
                    DB::rollBack();
                    return back()->withErrors(['start_at' => '이미 그 시간대에 예약이 있습니다.']);
                }

                $reservation = Reservation::create([
                    'room_id' => $room->id,
                    'start_at' => $startStr,
                    'end_at' => $endStr,
                    'key_code' => $this->generateKeyCode(),
                    'status' => 'confirmed',
                ]);

                // 예약-사용자 연결 (대표자)
                $reservation->users()->attach($user->id, ['is_representative' => true]);
                $createdIds[] = $reservation->id;
            }

            // 참여자 처리 (추후 구현)
            // if ($request->participants) { ... }

            DB::commit();

            if (count($createdIds) === 1) {
                return redirect()->route('reservation.confirm', $createdIds[0]);
            }

            $request->session()->flash('reservation_confirm_multi_ids', $createdIds);
            return redirect()->route('reservation.confirm_multi');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => '예약 중 오류가 발생했습니다: ' . $e->getMessage()]);
        }
    }

    /**
     * Show reservation confirmation page
     */
    public function confirm($id)
    {
        $reservation = Reservation::with(['room', 'users'])->findOrFail($id);
        return view('reservation.confirm', compact('reservation'));
    }

    /**
     * Show reservation confirmation page (multiple reservations)
     */
    public function confirmMulti(Request $request)
    {
        $ids = $request->session()->get('reservation_confirm_multi_ids', []);
        if (!is_array($ids) || empty($ids)) {
            return redirect()->route('reservation.my');
        }

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', '로그인이 필요합니다.');
        }

        $reservations = Reservation::with(['room', 'users'])
            ->whereIn('id', $ids)
            ->whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })
            ->orderBy('start_at')
            ->get();

        if ($reservations->isEmpty()) {
            return redirect()->route('reservation.my');
        }

        $date = $reservations->first()->start_at->format('Y년 m월 d일');

        return view('reservation.confirm-multi', compact('reservations', 'date'));
    }

    /**
     * Show reservation detail page
     */
    public function detail($id)
    {
        $reservation = Reservation::with(['room', 'users'])->findOrFail($id);
        return view('reservation.detail', compact('reservation'));
    }

    /**
     * Show my reservations
     */
    public function my()
    {
        $user = Auth::user();
        if ($user) {
            // 과거 임시 계정 예약(test@example.com)이 있으면 로그인 계정으로 자동 이관
            LegacyReservationMigrator::migrateFromTestUserIfNeeded($user);
        }
        
        // 읽지 않은 알림 수 가져오기
        $unreadCount = 0;
        if ($user) {
            $unreadCount = $user->notifications()->whereNull('read_at')->count();
        }

        $reservations = collect();
        if ($user) {
            $reservations = $user->reservations()
                ->with(['room', 'users'])
                ->where('status', 'confirmed')
                ->orderBy('start_at', 'desc')
                ->get();
        }

        // JavaScript에서 사용할 데이터 형식으로 변환
        $reservationsData = $reservations->map(function($r) {
            $statusText = $r->status === 'confirmed'
                ? '예약완료'
                : (($r->cancelled_by ?? null) === 'admin' ? '삭제됨' : '취소됨');

            return [
                'id' => $r->id,
                'date' => $r->start_at->format('Y-m-d'),
                'time' => ($r->start_at->format('A') === 'AM' ? '오전' : '오후') . ' ' . $r->start_at->format('g:i') . ' - ' . ($r->end_at->format('A') === 'AM' ? '오전' : '오후') . ' ' . $r->end_at->format('g:i'),
                'room_name' => $r->room->name,
                'status' => $r->status,
                'status_text' => $statusText,
                'start_at' => $r->start_at->toIso8601String(),
                'cancelled_by' => $r->cancelled_by,
                'cancel_reason' => $r->cancel_reason,
            ];
        });

        return view('reservation.my', compact('reservations', 'reservationsData', 'unreadCount'));
    }

    /**
     * Cancel reservation
     */
    public function destroy($id)
    {
        $reservation = Reservation::findOrFail($id);

        // 권한 체크 (임시: 모든 예약 취소 가능)
        // TODO: 대표자/관리자만 취소 가능하도록 권한 로직 보강

        if ($reservation->status !== 'confirmed') {
            return back()->withErrors(['error' => '이미 취소된 예약입니다.']);
        }

        $reservation->update([
            'status' => 'cancelled',
            'cancelled_at' => now('Asia/Seoul'),
            'cancelled_by' => 'user',
        ]);

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
