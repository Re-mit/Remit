<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class NewPasswordController extends Controller
{
    public function create(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'max:64', 'confirmed'],
        ]);

        try {
            $status = Password::reset(
                $validated,
                function ($user) use ($validated) {
                    $user->forceFill([
                        'password' => Hash::make($validated['password']),
                        'remember_token' => Str::random(60),
                    ])->save();

                    event(new PasswordReset($user));
                }
            );
        } catch (\Throwable $e) {
            Log::error('Password reset failed: exception', [
                'email' => $validated['email'] ?? null,
                'exception_class' => get_class($e),
                'exception_message' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => '비밀번호 재설정에 실패했습니다. 잠시 후 다시 시도해주세요.']);
        }

        if ($status === Password::PASSWORD_RESET) {
            return redirect()
                ->route('login')
                ->with('success', '비밀번호가 재설정되었습니다. 새 비밀번호로 로그인해주세요.');
        }

        // 토큰/이메일 오류 등 (사용자에게 적절한 메시지)
        Log::warning('Password reset failed: broker status', [
            'email' => $validated['email'] ?? null,
            'status' => $status,
        ]);

        return back()
            ->withInput()
            ->withErrors(['email' => __($status)]);
    }
}




