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
     * 월 단위 URL 등록 페이지 (3일 단위)
     */
    public function urls()
    {
        $this->authorizeAdmin();

        $user = auth()->user();
        $unreadCount = 0;
        if ($user) {
            $unreadCount = $user->notifications()->whereNull('read_at')->count();
        }

        $month = request('month'); // YYYY-MM
        if (!$month) {
            $month = now('Asia/Seoul')->format('Y-m');
        }
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            abort(400, 'Invalid month format.');
        }

        $start = Carbon::createFromFormat('Y-m', $month, 'Asia/Seoul')->startOfMonth()->startOfDay();
        $end = $start->copy()->endOfMonth()->endOfDay();
        $daysInMonth = $start->daysInMonth;
        // 고정 10개 블록:
        // 1) 1~3, 2) 4~6, ... 9) 25~27, 10) 28~말일(30/31/윤2월)
        $blockCount = 10;

        $prevMonth = $start->copy()->subMonth()->format('Y-m');
        $nextMonth = $start->copy()->addMonth()->format('Y-m');

        // 3일 단위 URL 블록 생성 (10번은 말일까지 포함되어 3~4일이 될 수 있음)
        $blocks = collect(range(0, $blockCount - 1))->map(function ($i) use ($start, $end) {
            $blockStartCarbon = $start->copy()->addDays($i * 3);
            $blockEndCarbon = $blockStartCarbon->copy()->addDays(2);
            if ($blockEndCarbon->gt($end)) {
                $blockEndCarbon = $end->copy();
            }

            $blockStart = $blockStartCarbon->toDateString();
            $blockEnd = $blockEndCarbon->toDateString();
            $existing = LockboxUrl::where('start_date', $blockStart)->first();

            return [
                'index' => $i + 1,
                'start_date' => $blockStart,
                'end_date' => $blockEnd,
                'url' => $existing?->url,
            ];
        });

        return view('admin.urls', compact('unreadCount', 'blocks', 'start', 'end', 'month', 'prevMonth', 'nextMonth', 'daysInMonth'));
    }

    /**
     * 월 단위 URL 저장 (3일 단위)
     */
    public function updateLockboxUrls(Request $request)
    {
        $this->authorizeAdmin();

        $month = $request->input('month'); // YYYY-MM
        if (!$month) {
            $month = now('Asia/Seoul')->format('Y-m');
        }
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            return back()->withErrors(['error' => '월 형식이 올바르지 않습니다. (YYYY-MM)']);
        }

        $start = Carbon::createFromFormat('Y-m', $month, 'Asia/Seoul')->startOfMonth()->startOfDay();
        $end = $start->copy()->endOfMonth()->endOfDay();
        // 고정 10개 블록
        $blockCount = 10;

        $expected = collect(range(0, $blockCount - 1))
            ->map(fn ($i) => $start->copy()->addDays($i * 3)->toDateString())
            ->all();

        $rules = [
            'urls' => ['required', 'array'],
            'month' => ['required', 'regex:/^\d{4}-\d{2}$/'],
        ];
        foreach ($expected as $d) {
            $rules["urls.$d"] = ['required', 'url', 'max:2048'];
        }

        $validated = $request->validate($rules);

        DB::beginTransaction();
        try {
            // 과거(동적 블록)로 저장된 같은 달 데이터 중, 이번에 기대하는 10개 start_date가 아닌 레코드는 제거
            // (예: 31일 달에서 start_date=31 같은 단독 블록이 남아있으면 날짜 매칭이 겹쳐 first() 결과가 불안정해짐)
            $monthStartDate = $start->copy()->toDateString();
            $monthEndDate = $end->copy()->toDateString();
            LockboxUrl::query()
                ->whereDate('start_date', '>=', $monthStartDate)
                ->whereDate('start_date', '<=', $monthEndDate)
                ->whereNotIn('start_date', $expected)
                ->delete();

            foreach ($expected as $d) {
                $endDateCarbon = Carbon::parse($d, 'Asia/Seoul')->addDays(2)->endOfDay();
                if ($endDateCarbon->gt($end)) {
                    $endDateCarbon = $end->copy();
                }
                $endDate = $endDateCarbon->toDateString();
                LockboxUrl::updateOrCreate(
                    ['start_date' => $d],
                    ['end_date' => $endDate, 'url' => $validated['urls'][$d]]
                );
            }

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

        return view('admin.users', compact('unreadCount', 'allowedEmails', 'admins', 'envAdminEmail'));
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

