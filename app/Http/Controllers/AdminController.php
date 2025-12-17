<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Notification;
use App\Models\User;
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
     * Display admin dashboard
     */
    public function index()
    {
        $this->authorizeAdmin();

        // 읽지 않은 알림 수 가져오기
        $user = auth()->user();
        $unreadCount = 0;
        if ($user) {
            $unreadCount = $user->notifications()->whereNull('read_at')->count();
        }

        $start = now()->startOfMonth();
        $end = now()->endOfMonth();

        $reservations = Reservation::with(['room', 'users'])
            ->where('status', 'confirmed')
            ->whereBetween('start_at', [$start, $end])
            ->orderBy('start_at')
            ->get();

        return view('admin.index', compact('unreadCount', 'reservations', 'start', 'end'));
    }

    /**
     * 한달치(이번 달) 예약 비밀번호 저장
     */
    public function updateKeycodes(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'keycodes' => 'required|array',
            'keycodes.*' => ['required', 'regex:/^\d{4}$/'],
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['keycodes'] as $reservationId => $code) {
                Reservation::whereKey($reservationId)->update([
                    'key_code' => $code,
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => '비밀번호 저장 중 오류가 발생했습니다: ' . $e->getMessage()]);
        }

        return back()->with('success', '이번 달 예약 비밀번호가 저장되었습니다.');
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

