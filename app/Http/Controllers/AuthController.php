<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    /**
     * Redirect to Google OAuth
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleGoogleCallback()
    {
        try {
            // 디버깅: 콜백 시작 확인
            \Log::info('=== Google OAuth Callback Started ===');
            
            $googleUser = Socialite::driver('google')->user();
            
            \Log::info('Google user info', [
                'id' => $googleUser->id,
                'email' => $googleUser->email,
                'name' => $googleUser->name
            ]);
            
            // 1. 가천 이메일 검증
            if (!str_ends_with($googleUser->email, '@gachon.ac.kr')) {
                \Log::warning('Non-Gachon email attempt', ['email' => $googleUser->email]);
                return redirect()->route('login')
                    ->with('error', '가천대학교 이메일(@gachon.ac.kr)로만 로그인할 수 있습니다.');
            }
            
            // 2. 허용된 학과 검증 (이름 뒤가 /금융수학과 또는 /금융·빅데이터학부)
            $allowedDepartments = ['/금융수학과', '/금융·빅데이터학부'];
            $isAllowedDepartment = false;
            foreach ($allowedDepartments as $department) {
                if (str_ends_with($googleUser->name, $department)) {
                    $isAllowedDepartment = true;
                    break;
                }
            }
            
            if (!$isAllowedDepartment) {
                \Log::warning('User filtered out - not allowed department', [
                    'name' => $googleUser->name,
                    'email' => $googleUser->email
                ]);
                return redirect()->route('login')
                    ->with('error', '해당 학과 학생만 이용할 수 있습니다. (금융수학과 또는 금융·빅데이터학부)');
            }
            
            // Find or create user
            $user = User::where('google_id', $googleUser->id)
                ->orWhere('email', $googleUser->email)
                ->first();

            if ($user) {
                \Log::info('Existing user found', ['user_id' => $user->id, 'email' => $user->email]);
                // Update Google ID and name if not set or changed
                $user->update([
                    'google_id' => $googleUser->id,
                    'name' => $googleUser->name, // Update name to keep it current
                ]);
            } else {
                \Log::info('Creating new user', ['email' => $googleUser->email]);
                // Create new user
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'role' => 'user',
                    'warning' => 0,
                ]);
                \Log::info('User created successfully', ['user_id' => $user->id, 'email' => $user->email]);
            }

            // Log the user in
            Auth::login($user);
            \Log::info('User logged in', ['user_id' => $user->id, 'session_id' => session()->getId()]);

            return redirect()->route('reservation.index')
                ->with('success', '환영합니다, ' . $user->name . '님!');

        } catch (\Exception $e) {
            \Log::error('Google OAuth callback error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('login')
                ->with('error', '로그인 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', '로그아웃되었습니다.');
    }
}
