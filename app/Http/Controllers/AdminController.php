<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Notification;
use App\Models\Notice;
use App\Models\User;
use App\Models\LockboxUrl;
use App\Models\AllowedEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    private function isEnvAdminEmail(string $email): bool
    {
        $adminEmail = config('admin.email');
        if (!$adminEmail) return false;
        return mb_strtolower(trim($adminEmail)) === mb_strtolower(trim($email));
    }

    private function authorizeAdmin(): void
    {
        $adminEmail = config('admin.email');

        if (!Auth::check()) {
            abort(403, '관리자만 접근할 수 있습니다.');
        }

        $user = Auth::user();
        $isEnvAdmin = $adminEmail && mb_strtolower(trim($adminEmail)) === mb_strtolower(trim($user->email));
        $isRoleAdmin = ($user->role ?? null) === 'admin';

        if (!$isEnvAdmin && !$isRoleAdmin) {
            abort(403, '관리자만 접근할 수 있습니다.');
        }
    }

    /**
     * 관리자 메뉴(대시보드)
     */
    public function dashboard()
    {
        $this->authorizeAdmin();

        // 읽지 않은 알림 수 가져오기
        $user = auth()->user();
        $unreadCount = 0;
        if ($user) {
            $unreadCount = $user->notifications()->whereNull('read_at')->count();
        }

        return view('admin.dashboard', compact('unreadCount'));
    }

    /**
     * 열쇠함 URL 등록 페이지 (전 기간 고정 1개)
     */
    public function urls()
    {
        $this->authorizeAdmin();

        $user = auth()->user();
        $unreadCount = 0;
        if ($user) {
            $unreadCount = $user->notifications()->whereNull('read_at')->count();
        }
        $lockboxUrl = LockboxUrl::query()->orderByDesc('id')->value('url');

        return view('admin.urls', compact('unreadCount', 'lockboxUrl'));
    }

    /**
     * 열쇠함 URL 저장 (전 기간 고정 1개)
     */
    public function updateLockboxUrls(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'url' => ['required', 'url', 'max:2048'],
        ]);

        // 전 기간 고정 1개 row로 유지 (기존 3일 단위 row는 정리)
        $fixedStart = '2000-01-01';
        $fixedEnd = '2099-12-31';

        DB::beginTransaction();
        try {
            LockboxUrl::query()
                ->where('start_date', '!=', $fixedStart)
                ->delete();

            LockboxUrl::updateOrCreate(
                ['start_date' => $fixedStart],
                ['end_date' => $fixedEnd, 'url' => $validated['url']]
            );

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'URL 저장 중 오류가 발생했습니다: ' . $e->getMessage()]);
        }

        return back()->with('success', '열쇠함 URL이 저장되었습니다.');
    }

    /**
     * 사용자(가입 허용 이메일) 관리 페이지
     */
    public function users()
    {
        $this->authorizeAdmin();

        $user = auth()->user();
        $unreadCount = 0;
        if ($user) {
            $unreadCount = $user->notifications()->whereNull('read_at')->count();
        }

        $allowedEmails = AllowedEmail::query()
            ->orderBy('email')
            ->paginate(30);

        $admins = User::query()
            ->where('role', 'admin')
            ->orderBy('email')
            ->get();

        $envAdminEmail = config('admin.email');

        // 사용자 목록(패널티/정지 관리용) - allowedEmails의 pagination과 충돌 방지 위해 page 파라미터 분리
        $users = User::query()
            ->orderByDesc('created_at')
            ->paginate(30, ['*'], 'members');

        return view('admin.users', compact('unreadCount', 'allowedEmails', 'admins', 'envAdminEmail', 'users'));
    }

    /**
     * 사용자 패널티(경고) +1
     * - 2회 이상이면 계정 정지(suspended_at 세팅)
     */
    public function addPenalty(Request $request, $id)
    {
        $this->authorizeAdmin();

        $target = User::query()->findOrFail($id);

        // 슈퍼/관리자 계정은 보호
        if ($this->isEnvAdminEmail($target->email) || ($target->role ?? null) === 'admin') {
            return back()->withErrors(['error' => '관리자 계정에는 패널티를 부여할 수 없습니다.']);
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ], [
            'reason.required' => '패널티 사유를 입력해주세요.',
        ]);

        $reason = trim($validated['reason']);

        DB::beginTransaction();

        $target->warning = (int) ($target->warning ?? 0) + 1;

        // 2회 이상이면 정지 처리
        $justSuspended = false;
        if ($target->warning >= 2 && empty($target->suspended_at)) {
            $target->suspended_at = now('Asia/Seoul');
            $justSuspended = true;
        }

        $target->save();

        // 대상 사용자에게 알림 생성
        $admin = Auth::user();
        $adminEmail = $admin?->email ?? '관리자';
        $message = "패널티가 부여되었습니다.\n"
            . "사유: {$reason}\n"
            . "누적: {$target->warning}회\n"
            . "부여자: {$adminEmail}";
        if ($justSuspended || !empty($target->suspended_at)) {
            $message .= "\n\n※ 패널티 2회 이상으로 계정이 정지되었습니다. 관리자에게 문의하세요.";
        }

        Notification::create([
            'user_id' => $target->id,
            'type' => 'penalty',
            'title' => '패널티가 부여되었습니다.',
            'message' => $message,
            'read_at' => null,
            'related_id' => null,
            'related_type' => null,
        ]);

        DB::commit();

        return back()->with('success', "패널티가 부여되었습니다. (누적 {$target->warning}회)");
    }

    /**
     * 패널티 초기화 + 정지 해제
     */
    public function resetPenalty($id)
    {
        $this->authorizeAdmin();

        $target = User::query()->findOrFail($id);

        if ($this->isEnvAdminEmail($target->email) || ($target->role ?? null) === 'admin') {
            return back()->withErrors(['error' => '관리자 계정은 초기화할 수 없습니다.']);
        }

        $target->warning = 0;
        $target->suspended_at = null;
        $target->save();

        return back()->with('success', '패널티가 초기화되었습니다.');
    }

    /**
     * 정지 해제(패널티도 0으로 초기화)
     */
    public function unsuspendUser($id)
    {
        $this->authorizeAdmin();

        $target = User::query()->findOrFail($id);

        if ($this->isEnvAdminEmail($target->email) || ($target->role ?? null) === 'admin') {
            return back()->withErrors(['error' => '관리자 계정은 해제할 수 없습니다.']);
        }

        $target->suspended_at = null;
        $target->warning = 0;
        $target->save();

        return back()->with('success', '계정 정지가 해제되었으며, 패널티가 초기화되었습니다.');
    }

    /**
     * 관리자 등록(권한 부여)
     */
    public function storeAdmin(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = AllowedEmail::normalize($validated['email']);

        $user = User::query()->where('email', $email)->first();
        if (!$user) {
            return back()->withErrors(['error' => '해당 이메일로 가입된 사용자가 없습니다. 먼저 회원가입을 완료해주세요.']);
        }

        $user->role = 'admin';
        $user->save();

        return back()->with('success', '관리자로 등록되었습니다.');
    }

    /**
     * 관리자 해제(권한 회수)
     */
    public function destroyAdmin($id)
    {
        $this->authorizeAdmin();

        $target = User::query()->findOrFail($id);

        $envAdminEmail = config('admin.email');
        if ($envAdminEmail && AllowedEmail::normalize($envAdminEmail) === AllowedEmail::normalize($target->email)) {
            return back()->withErrors(['error' => 'ENV로 지정된 슈퍼관리자는 해제할 수 없습니다.']);
        }

        $adminCount = User::query()->where('role', 'admin')->count();
        if ($adminCount <= 1) {
            return back()->withErrors(['error' => '최소 1명의 관리자는 남아 있어야 합니다.']);
        }

        $target->role = 'user';
        $target->save();

        return back()->with('success', '관리자 권한이 해제되었습니다.');
    }

    public function storeAllowedEmail(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'memo' => ['nullable', 'string', 'max:255'],
        ]);

        $email = AllowedEmail::normalize($validated['email']);

        if (!str_ends_with($email, '@gachon.ac.kr')) {
            return back()->withErrors(['error' => '가천대학교 이메일(@gachon.ac.kr)만 등록할 수 있습니다.']);
        }

        AllowedEmail::updateOrCreate(
            ['email' => $email],
            ['memo' => $validated['memo'] ?? null]
        );

        return back()->with('success', '허용 이메일이 등록되었습니다.');
    }

    public function destroyAllowedEmail($id)
    {
        $this->authorizeAdmin();

        AllowedEmail::whereKey($id)->delete();

        return back()->with('success', '허용 이메일이 삭제되었습니다.');
    }

    /**
     * 공지 작성 페이지
     */
    public function notices()
    {
        $this->authorizeAdmin();

        $user = auth()->user();
        $unreadCount = 0;
        if ($user) {
            $unreadCount = $user->notifications()->whereNull('read_at')->count();
        }

        $myNotices = collect();
        if ($user) {
            $myNotices = Notice::query()
                ->where('author_user_id', $user->id)
                ->orderByDesc('id')
                ->paginate(20);
        }

        return view('admin.notices', compact('unreadCount', 'myNotices'));
    }

    /**
     * 예약 관리 페이지 (현재 예약된 것들 조회/삭제)
     */
    public function reservations()
    {
        $this->authorizeAdmin();

        $user = auth()->user();
        $unreadCount = 0;
        if ($user) {
            $unreadCount = $user->notifications()->whereNull('read_at')->count();
        }

        $reservations = Reservation::with(['room', 'seat', 'users'])
            ->where('status', 'confirmed')
            ->where('end_at', '>', now('Asia/Seoul'))
            ->orderBy('start_at')
            ->paginate(30);

        return view('admin.reservations', compact('unreadCount', 'reservations'));
    }

    /**
     * 예약 내역 페이지 (최근 1달치: 완료/취소 내역 중심)
     */
    public function reservationsHistory(Request $request)
    {
        $this->authorizeAdmin();

        $user = auth()->user();
        $unreadCount = 0;
        if ($user) {
            $unreadCount = $user->notifications()->whereNull('read_at')->count();
        }

        $nowKst = now('Asia/Seoul');
        $months = max(1, (int) config('reservation.retention_months', 1));
        $cutoff = $nowKst->copy()->subMonthsNoOverflow($months)->startOfDay();

        $status = $request->query('status', 'all'); // all | confirmed | cancelled
        if (!in_array($status, ['all', 'confirmed', 'cancelled'], true)) {
            $status = 'all';
        }

        $query = Reservation::with(['room', 'seat', 'users'])
            ->where(function ($q) use ($cutoff, $nowKst) {
                // 최근 N개월 내 "완료된 예약" (end_at 기준)
                $q->whereBetween('end_at', [$cutoff, $nowKst])
                    // 또는 최근 N개월 내 "취소된 예약" (cancelled_at 기준; end_at이 미래여도 포함)
                    ->orWhere(function ($qq) use ($cutoff) {
                        $qq->whereNotNull('cancelled_at')
                            ->where('cancelled_at', '>=', $cutoff);
                    });
            })
            ->orderByDesc('start_at');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $reservations = $query->paginate(30)->withQueryString();

        return view('admin.reservations-history', compact('unreadCount', 'reservations', 'cutoff', 'status'));
    }

    public function destroyReservation(Request $request, $id)
    {
        $this->authorizeAdmin();

        $reservation = Reservation::findOrFail($id);
        if ($reservation->status !== 'confirmed') {
            return back()->withErrors(['error' => '이미 취소된 예약입니다.']);
        }

        $validated = $request->validate([
            'cancel_reason' => ['required', 'string', 'max:1000'],
        ]);

        DB::beginTransaction();
        try {
            $reservation->update([
                'status' => 'cancelled',
                'cancelled_at' => now('Asia/Seoul'),
                'cancelled_by' => 'admin',
                'cancel_reason' => $validated['cancel_reason'],
            ]);

            // 예약 당사자(참여자)에게 알림 발송
            $reservation->loadMissing(['users', 'room', 'seat']);
            $userIds = $reservation->users->pluck('id')->unique()->values();
            $now = now();

            if ($userIds->isNotEmpty()) {
                $timeLabel = $reservation->start_at
                    ? $reservation->start_at->format('Y년 m월 d일 ') .
                        (($reservation->start_at->format('A') === 'AM' ? '오전' : '오후') . ' ' . $reservation->start_at->format('g:i')) .
                        ' - ' .
                        (($reservation->end_at->format('A') === 'AM' ? '오전' : '오후') . ' ' . $reservation->end_at->format('g:i'))
                    : '';

                $roomLabel = $reservation->room?->name ?? '예약';
                $seatLabel = $reservation->seat?->label;
                $reason = $validated['cancel_reason'];

                $rows = [];
                foreach ($userIds as $uid) {
                    $rows[] = [
                        'user_id' => $uid,
                        'type' => 'reservation_deleted',
                        'title' => '예약이 삭제되었습니다.',
                        'message' => "{$roomLabel}" . ($seatLabel ? " ({$seatLabel})" : '') . "\n{$timeLabel}\n삭제 사유: {$reason}",
                        'read_at' => null,
                        'related_id' => $reservation->id,
                        'related_type' => Reservation::class,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                Notification::insert($rows);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => '예약 삭제 처리 중 오류가 발생했습니다: ' . $e->getMessage()]);
        }

        return back()->with('success', '예약이 취소되었습니다.');
    }

    /**
     * 알림 공지 저장 (모든 사용자에게)
     */
    public function storeNotice(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $author = Auth::user();
            if (!$author) {
                DB::rollBack();
                return back()->withErrors(['error' => '로그인이 필요합니다.']);
            }

            $notice = Notice::create([
                'author_user_id' => $author->id,
                'author_email' => $author->email,
                'title' => $validated['title'],
                'message' => $validated['message'],
            ]);

            // 모든 사용자에게 알림 생성
            User::query()
                ->select('id')
                ->chunkById(500, function ($users) use ($validated, $notice) {
                    $rows = [];
                    $now = now();
                    foreach ($users as $user) {
                        $rows[] = [
                            'user_id' => $user->id,
                            'type' => 'notice',
                            'title' => $validated['title'],
                            'message' => "작성자: {$notice->author_email}\n\n{$validated['message']}",
                            'created_at' => $now,
                            'updated_at' => $now,
                            'related_id' => $notice->id,
                            'related_type' => Notice::class,
                        ];
                    }
                    if (!empty($rows)) {
                        Notification::insert($rows);
                    }
                });

            DB::commit();

            return back()->with('success', '공지사항이 모든 사용자에게 발송되었습니다.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => '공지사항 발송 중 오류가 발생했습니다: ' . $e->getMessage()]);
        }
    }

    public function destroyNotice($id)
    {
        $this->authorizeAdmin();

        $user = Auth::user();
        if (!$user) {
            return back()->withErrors(['error' => '로그인이 필요합니다.']);
        }

        $notice = Notice::query()->findOrFail($id);

        // 본인이 작성한 공지만 삭제 가능
        if ((int) $notice->author_user_id !== (int) $user->id) {
            return back()->withErrors(['error' => '본인이 작성한 공지만 삭제할 수 있습니다.']);
        }

        DB::beginTransaction();
        try {
            Notification::query()
                ->where('related_type', Notice::class)
                ->where('related_id', $notice->id)
                ->delete();

            $notice->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => '공지 삭제 중 오류가 발생했습니다: ' . $e->getMessage()]);
        }

        return back()->with('success', '공지사항이 삭제되었습니다.');
    }
}

