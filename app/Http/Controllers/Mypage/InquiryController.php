<?php

namespace App\Http\Controllers\Mypage;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInquiryRequest;
use App\Mail\InquirySubmitted;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InquiryController extends Controller
{
    /**
     * 문의하기 작성 페이지
     */
    public function create()
    {
        $user = auth()->user();
        $unreadCount = 0;
        if ($user) {
            $unreadCount = $user->notifications()->whereNull('read_at')->count();
        }

        return view('mypage.inquiry.create', compact('unreadCount'));
    }

    /**
     * 문의하기 전송 (관리자 이메일로 발송)
     */
    public function store(StoreInquiryRequest $request)
    {
        $user = $request->user();
        $validated = $request->validated();

        $adminEmail = config('contact.admin_email');
        if (!$adminEmail) {
            Log::error('Inquiry send failed: admin email is not configured', [
                'user_id' => $user?->id,
                'user_email' => $user?->email,
                'title' => $validated['title'] ?? null,
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => '문의 전송에 실패했습니다. (관리자 이메일 설정이 필요합니다)']);
        }

        try {
            $submittedAt = now('Asia/Seoul');
            Mail::to($adminEmail)->send(new InquirySubmitted(
                user: $user,
                title: $validated['title'],
                content: $validated['content'],
                submittedAt: $submittedAt,
                appName: config('app.name'),
                appUrl: config('app.url'),
                env: config('app.env'),
            ));
        } catch (\Throwable $e) {
            Log::error('Inquiry send failed: mail exception', [
                'user_id' => $user?->id,
                'user_name' => $user?->name,
                'user_email' => $user?->email,
                'admin_email' => $adminEmail,
                'title' => $validated['title'] ?? null,
                'exception_class' => get_class($e),
                'exception_message' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => '문의 전송에 실패했습니다. 잠시 후 다시 시도해주세요.']);
        }

        return redirect()
            ->route('mypage.index')
            ->with('success', '문의가 전송되었습니다. 감사합니다.');
    }
}


