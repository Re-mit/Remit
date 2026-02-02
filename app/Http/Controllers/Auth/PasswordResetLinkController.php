<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;

class PasswordResetLinkController extends Controller
{
    public function create()
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        try {
            $status = Password::sendResetLink($validated);
        } catch (\Throwable $e) {
            Log::error('Password reset link send failed: exception', [
                'email' => $validated['email'] ?? null,
                'exception_class' => get_class($e),
                'exception_message' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => '재설정 링크 발송에 실패했습니다. 잠시 후 다시 시도해주세요.']);
        }

        // 보안: 이메일 존재 여부를 노출하지 않기 (항상 동일 메시지)
        // - INVALID_USER여도 동일한 "보냈습니다" 메시지로 처리
        // - 단, 시스템 예외는 위 try/catch에서 별도 처리
        if (!in_array($status, [Password::RESET_LINK_SENT, Password::INVALID_USER], true)) {
            Log::warning('Password reset link send returned unexpected status', [
                'email' => $validated['email'] ?? null,
                'status' => $status,
            ]);
        }

        return back()->with('status', '입력하신 이메일로 비밀번호 재설정 링크를 보냈습니다.');
    }
}







