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
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            // Check if user name contains the allowed filter (e.g., "철학과")
            $allowedFilter = env('ALLOWED_USER_FILTER');
            if ($allowedFilter && !str_contains($googleUser->name, $allowedFilter)) {
                return redirect()->route('login')
                    ->with('error', '접근 권한이 없습니다. 허용된 사용자만 이용 가능합니다. (필터: ' . $allowedFilter . ')');
            }
            
            // Find or create user
            $user = User::where('google_id', $googleUser->id)
                ->orWhere('email', $googleUser->email)
                ->first();

            if ($user) {
                // Update Google ID and name if not set or changed
                $user->update([
                    'google_id' => $googleUser->id,
                    'name' => $googleUser->name, // Update name to keep it current
                ]);
            } else {
                // Create new user
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'role' => 'user',
                    'warning' => 0,
                ]);
            }

            // Log the user in
            Auth::login($user);

            return redirect()->route('reservation.index')
                ->with('success', '환영합니다, ' . $user->name . '님!');

        } catch (\Exception $e) {
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
