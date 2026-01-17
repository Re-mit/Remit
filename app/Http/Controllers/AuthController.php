<?php

namespace App\Http\Controllers;

use App\Mail\EmailVerificationCodeMail;
use App\Models\AllowedEmail;
use App\Models\EmailVerificationCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

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
     * 회원가입 이메일 인증번호 발송
     */
    public function sendRegisterCode(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = AllowedEmail::normalize($validated['email']);

        if (!str_ends_with($email, '@gachon.ac.kr')) {
            return back()
                ->withInput()
                ->with('error', '가천대학교 이메일(@gachon.ac.kr)로만 인증번호를 발송할 수 있습니다.');
        }

        if (!AllowedEmail::isAllowed($email)) {
            return back()
                ->withInput()
                ->with('error', '해당 학과가 아닙니다. 관리자에게 문의하세요');
        }

        $row = EmailVerificationCode::where('email', $email)->first();

        // 재전송 제한: 60초
        if ($row && $row->sent_at && $row->sent_at->gt(now()->subSeconds(60))) {
            return back()
                ->withInput()
                ->with('error', '인증번호는 1분에 한 번만 재발송할 수 있습니다. 잠시 후 다시 시도해주세요.');
        }

        $code = (string) random_int(100000, 999999);
        $expiresMinutes = 10;

        EmailVerificationCode::updateOrCreate(
            ['email' => $email],
            [
                'code_hash' => hash('sha256', $code),
                'expires_at' => now()->addMinutes($expiresMinutes),
                'sent_at' => now(),
                'attempts' => 0,
                'verified_at' => null,
            ]
        );

        try {
            Mail::to($email)->send(new EmailVerificationCodeMail($code, $expiresMinutes));
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', '메일 발송에 실패했습니다. MAIL 설정을 확인해주세요. (' . $e->getMessage() . ')');
        }

        // 세션에 진행 상태 저장 (이메일 변경 시 인증 무효화)
        $request->session()->forget(['register_verified_email', 'register_verified_at']);
        $request->session()->put('register_pending_email', $email);

        return back()
            ->withInput()
            ->with('success', '인증번호를 발송했습니다. 이메일을 확인해주세요.');
    }

    /**
     * 회원가입 이메일 인증번호 검증
     */
    public function verifyRegisterCode(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        $email = AllowedEmail::normalize($validated['email']);
        $code = $validated['code'];

        $row = EmailVerificationCode::where('email', $email)->first();
        if (!$row) {
            return back()
                ->withInput()
                ->with('error', '인증번호를 먼저 발송해주세요.');
        }

        if ($row->verified_at) {
            $request->session()->put('register_verified_email', $email);
            $request->session()->put('register_verified_at', now());
            $request->session()->forget('register_pending_email');
            return back()->withInput()->with('success', '이미 인증이 완료된 이메일입니다.');
        }

        if ($row->expires_at->lte(now())) {
            return back()
                ->withInput()
                ->with('error', '인증번호가 만료되었습니다. 다시 발송해주세요.');
        }

        // 시도 횟수 제한 (기본 10회)
        if ($row->attempts >= 10) {
            return back()
                ->withInput()
                ->with('error', '인증 시도 횟수가 초과되었습니다. 인증번호를 다시 발송해주세요.');
        }

        $row->attempts = $row->attempts + 1;
        $row->save();

        if (!hash_equals($row->code_hash, hash('sha256', $code))) {
            return back()
                ->withInput()
                ->with('error', '인증번호가 올바르지 않습니다.');
        }

        $row->verified_at = now();
        $row->save();

        $request->session()->put('register_verified_email', $email);
        $request->session()->put('register_verified_at', now());
        $request->session()->forget('register_pending_email');

        return back()
            ->withInput()
            ->with('success', '이메일 인증이 완료되었습니다.');
    }

    /**
     * 회원가입 처리 (세션)
     * - 기존 Google 계정이 있더라도 password가 비어있으면 "비밀번호 설정"으로 처리
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'student_id' => ['required', 'string', 'max:20', 'regex:/^\d+$/'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:4', 'max:12', 'confirmed'],
            'agree_terms' => ['required', 'accepted'],
        ]);

        $validated['email'] = AllowedEmail::normalize($validated['email']);

        // 가천 이메일만 허용
        if (!str_ends_with($validated['email'], '@gachon.ac.kr')) {
            return back()
                ->withInput()
                ->with('error', '가천대학교 이메일(@gachon.ac.kr)로만 회원가입할 수 있습니다.');
        }

        if (!AllowedEmail::isAllowed($validated['email'])) {
            return back()
                ->withInput()
                ->with('error', '해당 학과가 아닙니다. 관리자에게 문의하세요');
        }

        // 이메일 인증 여부 확인 (세션 기반)
        $verifiedEmail = $request->session()->get('register_verified_email');
        if (!$verifiedEmail || $verifiedEmail !== $validated['email']) {
            return back()
                ->withInput()
                ->with('error', '회원가입을 위해 이메일 인증을 먼저 완료해주세요.');
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
        $user->student_id = $validated['student_id'];
        $user->password = Hash::make($validated['password']);
        $user->google_id = null; // 더 이상 사용하지 않음
        $user->role = $user->role ?: 'user';
        $user->warning = $user->warning ?? 0;
        $user->email_verified_at = now();
        $user->terms_agreed_at = $user->terms_agreed_at ?: now('Asia/Seoul');
        $user->save();

        // 인증코드 기록 정리 및 세션 정리
        EmailVerificationCode::where('email', $validated['email'])->delete();
        $request->session()->forget(['register_pending_email', 'register_verified_email', 'register_verified_at']);

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
