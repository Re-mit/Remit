<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Display admin dashboard
     */
    public function index()
    {
        // 읽지 않은 알림 수 가져오기
        $user = auth()->user();
        $unreadCount = 0;
        if ($user) {
            $unreadCount = $user->notifications()->whereNull('read_at')->count();
        }

        return view('admin.index', compact('unreadCount'));
    }

    /**
     * 한달치 비밀번호 저장
     */
    public function storeKeyCodes(Request $request)
    {
        $validated = $request->validate([
            'keycodes' => 'required|array',
            'keycodes.*.date' => 'required|date',
            'keycodes.*.code' => 'required|string|size:4',
        ]);

        // 세션에 한달치 비밀번호 저장 (실제로는 데이터베이스나 캐시에 저장)
        session(['monthly_keycodes' => $validated['keycodes']]);

        return back()->with('success', '한달치 비밀번호가 저장되었습니다.');
    }

    /**
     * 알림 공지 저장 (모든 사용자에게)
     */
    public function storeNotice(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            // 모든 사용자에게 알림 생성
            $users = User::all();
            foreach ($users as $user) {
                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'notice',
                    'title' => $validated['title'],
                    'message' => $validated['message'],
                ]);
            }

            DB::commit();

            return back()->with('success', '공지사항이 모든 사용자에게 발송되었습니다.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => '공지사항 발송 중 오류가 발생했습니다: ' . $e->getMessage()]);
        }
    }
}

