<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\Seat;
use App\Models\User;
use App\Models\AllowedEmail;
use App\Models\Notification;
use App\Support\LegacyReservationMigrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    private function seatNumberFromLabel(?string $label): ?int
    {
        if (!$label) return null;
        if (preg_match('/^(\d+)\s*번$/u', trim($label), $m)) {
            return (int) $m[1];
        }
        return null;
    }

    private function validateGroupMemberEmailsForSeat3(Request $request, User $representative): array
    {
        $raw = $request->input('group_member_emails', []);
        if (!is_array($raw)) {
            throw new \InvalidArgumentException('동반 이용자 이메일 형식이 올바르지 않습니다.');
        }

        $emails = [];
        foreach ($raw as $e) {
            $e = is_string($e) ? trim(mb_strtolower($e)) : '';
            if ($e === '') continue;
            $emails[] = $e;
        }

        $emails = array_values(array_unique($emails));

        if (count($emails) < 1) {
            throw new \InvalidArgumentException('3번 좌석(단체석)은 함께 이용할 사람의 이메일을 최소 1명 이상 입력해주세요.');
        }
        if (count($emails) > 4) {
            throw new \InvalidArgumentException('동반 이용자 이메일은 최대 4명까지 입력할 수 있습니다. (총 5명 이용)');
        }

        $repEmail = mb_strtolower(trim((string) $representative->email));
        foreach ($emails as $e) {
            if (!filter_var($e, FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException('이메일 형식이 올바르지 않습니다.');
            }
            if (!str_ends_with($e, '@gachon.ac.kr')) {
                throw new \InvalidArgumentException('가천대학교 이메일(@gachon.ac.kr)만 입력할 수 있습니다.');
            }
            if ($repEmail !== '' && $e === $repEmail) {
                throw new \InvalidArgumentException('동반 이용자 이메일에는 예약자 본인 이메일을 제외해주세요.');
            }

            // 허용 이메일 목록(allowed_emails) 또는 실제 사용자(users)만 허용
            $isUser = User::query()->where('email', $e)->exists();
            $isAllowed = AllowedEmail::query()->where('email', AllowedEmail::normalize($e))->exists();
            $adminEmail = config('admin.email');
            $isAdminEmail = $adminEmail && AllowedEmail::normalize($adminEmail) === AllowedEmail::normalize($e);
            if (!$isUser && !$isAllowed && !$isAdminEmail) {
                throw new \InvalidArgumentException("{$e}은(는) 사용자가 아닙니다.");
            }
        }

        return $emails;
    }

    private function notifyNoiseToOverlappingUsers(
        int $roomId,
        int $groupSeatId,
        array $segments, // [['start_at' => 'Y-m-d\TH:i:s', 'end_at' => 'Y-m-d\TH:i:s'], ...]
        array $excludeReservationIds,
        int $actorUserId,
        int $relatedReservationId
    ): void {
        $userIds = DB::table('reservation_users')
            ->join('reservations', 'reservation_users.reservation_id', '=', 'reservations.id')
            ->where('reservations.room_id', $roomId)
            ->where('reservations.status', 'confirmed')
            ->whereNotNull('reservations.seat_id')
            ->where('reservations.seat_id', '!=', $groupSeatId)
            ->when(!empty($excludeReservationIds), function ($q) use ($excludeReservationIds) {
                $q->whereNotIn('reservations.id', $excludeReservationIds);
            })
            ->where(function ($q) use ($segments) {
                foreach ($segments as $seg) {
                    $startStr = $seg['start_at'];
                    $endStr = $seg['end_at'];
                    $q->orWhere(function ($qq) use ($startStr, $endStr) {
                        $qq->where('reservations.start_at', '<', $endStr)
                            ->where('reservations.end_at', '>', $startStr);
                    });
                }
            })
            ->distinct()
            ->pluck('reservation_users.user_id')
            ->all();

        $userIds = array_values(array_filter($userIds, fn ($id) => (int) $id !== (int) $actorUserId));
        if (empty($userIds)) return;

        foreach ($userIds as $uid) {
            Notification::create([
                'user_id' => (int) $uid,
                'type' => 'notice',
                'title' => '소음 안내',
                'message' => '3번 좌석(단체석)이 예약되어 소음이 발생할 수 있습니다.',
                'related_id' => $relatedReservationId,
                'related_type' => Reservation::class,
            ]);
        }
    }

    private function getOrCreateDefaultRoom(): Room
    {
        // 기본 방 (가천관 622호) 가져오기 (레거시 데이터 '622호'도 허용)
        // 다른 PC/환경에서 seed가 안 되어 rooms 테이블이 비어있을 수 있으므로, 없으면 자동 생성
        $room = Room::whereIn('name', ['가천관 622호', '622호'])->first();
        if (!$room) {
            $room = Room::updateOrCreate(
                ['name' => '가천관 622호'],
                ['description' => '학과 공용 스터디룸']
            );
        }

        return $room;
    }

    private function ensureSeats(Room $room): void
    {
        $count = (int) config('reservation.default_seat_count', 6);
        if ($count < 1) {
            $count = 1;
        }
        $max = (int) config('reservation.max_seat_count', 6);
        if ($max > 0) {
            $count = min($count, $max);
        }

        // 1~count 생성/활성화
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

        // count 초과 좌석은 비활성화 (상한 보장)
        Seat::where('room_id', $room->id)
            ->where('is_active', true)
            ->where(function ($q) use ($count) {
                for ($i = $count + 1; $i <= 50; $i++) {
                    $q->orWhere('label', "{$i}번");
                }
            })
            ->update(['is_active' => false]);
    }

    public function availableSeats(Request $request)
    {
        $segmentsRaw = $request->input('segments');
        $segments = json_decode($segmentsRaw, true);

        if (!is_array($segments) || empty($segments)) {
            return response()->json(['message' => '예약 시간이 올바르지 않습니다.'], 422);
        }

        foreach ($segments as $seg) {
            if (!is_array($seg) || empty($seg['start_at']) || empty($seg['end_at'])) {
                return response()->json(['message' => '예약 시간이 올바르지 않습니다.'], 422);
            }
            if (!strtotime($seg['start_at']) || !strtotime($seg['end_at'])) {
                return response()->json(['message' => '예약 시간이 올바르지 않습니다.'], 422);
            }
        }

        $room = $this->getOrCreateDefaultRoom();
        $this->ensureSeats($room);

        $activeSeats = Seat::where('room_id', $room->id)
            ->where('is_active', true)
            ->orderBy('id')
            ->get(['id', 'label']);

        // OR 조건으로 "어느 구간이든 겹치면 예약된 좌석"으로 처리
        $reservedSeatIds = Reservation::where('room_id', $room->id)
            ->where('status', 'confirmed')
            ->whereNotNull('seat_id')
            ->where(function ($q) use ($segments) {
                foreach ($segments as $seg) {
                    $startStr = (new \DateTime($seg['start_at']))->format('Y-m-d\TH:i:s');
                    $endStr = (new \DateTime($seg['end_at']))->format('Y-m-d\TH:i:s');
                    $q->orWhere(function ($qq) use ($startStr, $endStr) {
                        $qq->where('start_at', '<', $endStr)
                            ->where('end_at', '>', $startStr);
                    });
                }
            })
            ->distinct()
            ->pluck('seat_id')
            ->all();

        $seats = $activeSeats->map(function ($s) use ($reservedSeatIds) {
            return [
                'id' => $s->id,
                'label' => $s->label,
                'is_available' => !in_array($s->id, $reservedSeatIds, true),
            ];
        })->values();
        $availableSeatsCount = $seats->where('is_available', true)->count();

        return response()->json([
            'room_id' => $room->id,
            'total_seats' => $activeSeats->count(),
            'available_seats_count' => $availableSeatsCount,
            'seats' => $seats,
        ]);
    }

    /**
     * Display reservation form and list
     */
    /**
     * Display reservation form (예약하기)
     */
    public function index()
    {
        // 예약하기 화면에서는 "이미 시작했지만 아직 끝나지 않은 예약"도
        // 예약된 시간대로 표시되어야 하므로 start_at 기준 필터링을 쓰면 안 됩니다.
        // (예: 13~16 예약이 있고 14시에 접속하면, start_at < now()라서 누락되어 15시가 빈칸으로 보일 수 있음)
        $nowKst = now('Asia/Seoul');
        $rangeStart = $nowKst->copy()->startOfDay(); // 오늘 00:00 (KST)
        $rangeEnd = $nowKst->copy()->addDays(7)->endOfDay(); // 7일 후 23:59:59 (KST)

        $reservations = Reservation::with(['room', 'users'])
            ->where('status', 'confirmed')
            // 조회 범위(오늘~7일)에 걸쳐있는 예약만 포함 + "진행중(end_at > now)" 예약도 포함
            // 겹침 조건: start_at < rangeEnd AND end_at > rangeStart
            ->where('start_at', '<', $rangeEnd)
            ->where('end_at', '>', $rangeStart)
            ->orderBy('start_at')
            ->get();

        // 읽지 않은 알림 수 가져오기
        $user = Auth::user();
        $unreadCount = 0;
        $unreadNotices = collect();
        if ($user) {
            $unreadCount = $user->notifications()->whereNull('read_at')->count();
            // 예약 페이지 상단 토스트용: 공지/패널티 알림(unread)만 가져오기
            $unreadNotices = Notification::query()
                ->where('user_id', $user->id)
                ->whereNull('read_at')
                ->whereIn('type', ['notice', 'penalty'])
                ->orderByDesc('created_at')
                ->limit(5)
                ->get(['id', 'type', 'title', 'message', 'created_at']);
        }

        return view('reservation.index', compact('reservations', 'unreadCount', 'unreadNotices'));
    }

    /**
     * Store a new reservation
     */
    public function store(Request $request)
    {
        $seatIdRaw = $request->input('seat_id');
        if (empty($seatIdRaw) || !ctype_digit((string) $seatIdRaw)) {
            return back()->withErrors(['seat_id' => '좌석을 선택해주세요.']);
        }
        $seatId = (int) $seatIdRaw;

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

            $room = $this->getOrCreateDefaultRoom();
            $this->ensureSeats($room);

            $seat = Seat::where('id', $seatId)
                ->where('room_id', $room->id)
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            if (!$seat) {
                DB::rollBack();
                return back()->withErrors(['seat_id' => '선택한 좌석이 올바르지 않습니다.']);
            }

            $seatNumber = $this->seatNumberFromLabel($seat->label);
            $groupMemberEmails = [];
            $groupMemberUserIds = [];
            if ($seatNumber === 3) {
                try {
                    $groupMemberEmails = $this->validateGroupMemberEmailsForSeat3($request, $user);
                } catch (\InvalidArgumentException $e) {
                    DB::rollBack();
                    return back()->withErrors(['group_member_emails' => $e->getMessage()])->withInput();
                }

                // 실제 가입된 사용자만 pivot에 붙일 수 있으므로(users 기준)
                if (!empty($groupMemberEmails)) {
                    $groupMemberUserIds = User::query()
                        ->whereIn('email', $groupMemberEmails)
                        ->pluck('id')
                        ->all();
                }
            }

            $createdIds = [];
            $createdSegments = [];

            foreach ($parsed as $seg) {
                $startStr = $seg['start_at']->format('Y-m-d\TH:i:s');
                $endStr = $seg['end_at']->format('Y-m-d\TH:i:s');

                // 중복 예약 확인 (동시성 제어) - 같은 좌석에서만 겹침 금지
                $overlapping = Reservation::where('room_id', $room->id)
                    ->where('seat_id', $seat->id)
                    ->where('status', 'confirmed')
                    ->where('start_at', '<', $endStr)
                    ->where('end_at', '>', $startStr)
                    ->lockForUpdate()
                    ->exists();

                if ($overlapping) {
                    DB::rollBack();
                    return back()->withErrors(['seat_id' => '선택한 좌석은 이미 해당 시간대에 예약되어 있습니다.']);
                }

                $reservation = Reservation::create([
                    'room_id' => $room->id,
                    'seat_id' => $seat->id,
                    'group_member_emails' => ($seatNumber === 3 ? $groupMemberEmails : null),
                    'start_at' => $startStr,
                    'end_at' => $endStr,
                    'key_code' => $this->generateKeyCode(),
                    'status' => 'confirmed',
                ]);

                // 예약-사용자 연결 (대표자)
                $reservation->users()->attach($user->id, ['is_representative' => true]);

                // 단체석: 동반 이용자도 예약 사용자로 연결(내 예약에 표시)
                if ($seatNumber === 3 && !empty($groupMemberUserIds)) {
                    foreach ($groupMemberUserIds as $uid) {
                        $reservation->users()->syncWithoutDetaching([
                            (int) $uid => ['is_representative' => false],
                        ]);
                    }
                }
                $createdIds[] = $reservation->id;
                $createdSegments[] = ['start_at' => $startStr, 'end_at' => $endStr];
            }

            // 참여자 처리 (추후 구현)
            // if ($request->participants) { ... }

            // 좌석 3(단체석) 예약 확정 순간: 같은 시간대에 이미 다른 좌석을 예약한 사용자에게 소음 안내 알림 발송
            if ($seatNumber === 3 && !empty($createdIds) && !empty($createdSegments)) {
                $this->notifyNoiseToOverlappingUsers(
                    roomId: (int) $room->id,
                    groupSeatId: (int) $seat->id,
                    segments: $createdSegments,
                    excludeReservationIds: $createdIds,
                    actorUserId: (int) $user->id,
                    relatedReservationId: (int) $createdIds[0],
                );
            }

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
        $reservation = Reservation::with(['room', 'seat', 'users'])->findOrFail($id);
        
        // 읽지 않은 알림 수 가져오기
        $user = Auth::user();
        $unreadCount = 0;
        if ($user) {
            $unreadCount = $user->notifications()->whereNull('read_at')->count();
        }

        return view('reservation.confirm', compact('reservation', 'unreadCount'));
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

        // 읽지 않은 알림 수 가져오기
        $unreadCount = $user->notifications()->whereNull('read_at')->count();

        $reservations = Reservation::with(['room', 'seat', 'users'])
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

        return view('reservation.confirm-multi', compact('reservations', 'date', 'unreadCount'));
    }

    /**
     * Show reservation detail page
     */
    public function detail($id)
    {
        $reservation = Reservation::with(['room', 'seat', 'users'])->findOrFail($id);
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
                ->with(['room', 'seat', 'users'])
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
                'seat_label' => $r->seat?->label,
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

        // 권한 체크: 예약 참여자(대표/동반) 또는 관리자만 취소 가능
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', '로그인이 필요합니다.');
        }

        $isParticipant = $reservation->users()->where('users.id', $user->id)->exists();
        $envAdminEmail = config('admin.email');
        $isAdmin = ($user->role ?? null) === 'admin'
            || ($envAdminEmail && AllowedEmail::normalize($envAdminEmail) === AllowedEmail::normalize((string) $user->email));

        if (!$isParticipant && !$isAdmin) {
            return back()->withErrors(['error' => '해당 예약을 취소할 권한이 없습니다.']);
        }

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
