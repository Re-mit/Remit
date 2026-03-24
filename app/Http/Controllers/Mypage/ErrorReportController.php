<?php

namespace App\Http\Controllers\Mypage;

use App\Http\Controllers\Controller;
use App\Models\ErrorReport;
use Illuminate\Http\Request;

class ErrorReportController extends Controller
{
    /**
     * 서비스 개선 페이지
     */
    public function create()
    {
        $user = auth()->user();
        $unreadCount = 0;
        if ($user) {
            $unreadCount = $user->notifications()->whereNull('read_at')->count();
        }

        return view('mypage.error-report.create', compact('unreadCount'));
    }

    /**
     * 서비스 개선 저장
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:5000'],
        ]);

        ErrorReport::create([
            'user_id' => $user->id,
            'title' => $validated['title'],
            'content' => $validated['content'],
        ]);

        return redirect()
            ->route('mypage.index')
            ->with('success', '전달되었습니다. 감사합니다.');
    }
}


