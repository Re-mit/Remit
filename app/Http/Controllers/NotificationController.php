<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use Carbon\Carbon;

class NotificationController extends Controller
{
    /**
     * Display the notification list
     */
    public function index()
    {
        $userId = Auth::id();
        if (!$userId) {
            return redirect()->route('login');
        }

        // 알림 페이지에 들어오면 "확인"한 것으로 간주: 모두 읽음 처리
        $now = now();
        Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => $now]);

        $notifications = Notification::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function ($notification) {
                $date = $notification->created_at;
                if ($date->isToday()) {
                    return '오늘';
                }
                return $date->format('Y년 m월 d일');
            });

        return view('notification.index', compact('notifications'));
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->findOrFail($id);
        
        $notification->update(['read_at' => now()]);
        
        return response()->json(['success' => true]);
    }
}
