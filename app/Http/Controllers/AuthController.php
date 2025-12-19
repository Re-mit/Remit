<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * 로그인 화면
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * 로그인 처리 (세션)
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // 가천 이메일만 허용
        if (!str_ends_with($validated['email'], '@gachon.ac.kr')) {
            return back()
                ->withInput()
                ->with('error', '가천대학교 이메일(@gachon.ac.kr)로만 로그인할 수 있습니다.');
        }

        // 비밀번호 미설정(기존 Google 계정 등) 안내
        $existing = User::where('email', $validated['email'])->first();
        if ($existing && empty($existing->password)) {
            return back()
                ->withInput()
                ->with('error', '해당 계정은 비밀번호가 설정되지 않았습니다. 회원가입(비밀번호 설정)으로 진행해주세요.');
        }

        if (!Auth::attempt(['email' => $validated['email'], 'password' => $validated['password']])) {
            return back()
                ->withInput()
                ->with('error', '이메일 또는 비밀번호가 올바르지 않습니다.');
        }

        $request->session()->regenerate();

        return redirect()->route('reservation.index')
            ->with('success', '로그인되었습니다.');
    }

    /**
     * 회원가입 화면
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * 회원가입 처리 (세션)
     * - 기존 Google 계정이 있더라도 password가 비어있으면 "비밀번호 설정"으로 처리
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // 가천 이메일만 허용
        if (!str_ends_with($validated['email'], '@gachon.ac.kr')) {
            return back()
                ->withInput()
                ->with('error', '가천대학교 이메일(@gachon.ac.kr)로만 회원가입할 수 있습니다.');
        }

        $user = User::where('email', $validated['email'])->first();

        // 이미 가입된 계정 처리
        if ($user && !empty($user->password)) {
            return back()
                ->withInput()
                ->with('error', '이미 가입된 이메일입니다. 로그인해주세요.');
        }

        // 신규 생성 or 기존(구글) 계정에 비밀번호 설정
        if (!$user) {
            $user = new User();
            $user->email = $validated['email'];
        }

        $user->name = $validated['name'];
        $user->password = Hash::make($validated['password']);
        $user->google_id = null; // 더 이상 사용하지 않음
        $user->role = $user->role ?: 'user';
        $user->warning = $user->warning ?? 0;
        $user->save();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('reservation.index')
            ->with('success', '회원가입 및 로그인 완료!');
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
