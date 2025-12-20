<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Notification;
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

        if (!Auth::check() || !$adminEmail || Auth::user()->email !== $adminEmail) {
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
     * 30일(3일 단위) URL 등록 페이지
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
        $end = $start->copy()->addDays(29)->endOfDay();

        $prevMonth = $start->copy()->subMonth()->format('Y-m');
        $nextMonth = $start->copy()->addMonth()->format('Y-m');

        // 3일 단위(10개) URL 블록 생성
        $blocks = collect(range(0, 9))->map(function ($i) use ($start) {
            $blockStart = $start->copy()->addDays($i * 3)->toDateString();
            $blockEnd = $start->copy()->addDays($i * 3 + 2)->toDateString();
            $existing = LockboxUrl::where('start_date', $blockStart)->first();

            return [
                'index' => $i + 1,
                'start_date' => $blockStart,
                'end_date' => $blockEnd,
                'url' => $existing?->url,
            ];
        });

        return view('admin.urls', compact('unreadCount', 'blocks', 'start', 'end', 'month', 'prevMonth', 'nextMonth'));
    }

    /**
     * 3일 단위(10개) URL 저장 (총 30일)
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
        $expected = collect(range(0, 9))
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
            foreach ($expected as $d) {
                $endDate = Carbon::parse($d, 'Asia/Seoul')->addDays(2)->toDateString();
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

        return back()->with('success', '30일(3일 단위) 열쇠함 URL이 저장되었습니다.');
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

        return view('admin.users', compact('unreadCount', 'allowedEmails'));
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

        return view('admin.notices', compact('unreadCount'));
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

            // 모든 사용자에게 알림 생성
            User::query()
                ->select('id')
                ->chunkById(500, function ($users) use ($validated) {
                    $rows = [];
                    $now = now();
                    foreach ($users as $user) {
                        $rows[] = [
                            'user_id' => $user->id,
                            'type' => 'notice',
                            'title' => $validated['title'],
                            'message' => $validated['message'],
                            'created_at' => $now,
                            'updated_at' => $now,
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
}

