<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\IpUtils;

class CheckInternalIp
{
    /**

     * 내부망만 서비스 접근 허용 (방법 A)
     * - 내부망: 172.25.128.0/21
     * - 예외: Google OAuth 시작/콜백 경로는 외부 IP에서도 허용
     * - 로컬 개발: 127.0.0.1, ::1 허용
     */
    public function handle(Request $request, Closure $next)
    {
        // OAuth 예외 (구글 콜백은 외부에서 들어올 수 있음)
        if ($request->is('auth/google') || $request->is('auth/google/*')) {
            return $next($request);
        }

        // 필요 시 로그인 화면도 외부에서 열어야 한다면 예외로 둘 수 있음
        // if ($request->is('login')) {
        //     return $next($request);
        // }

        $allowed = [
            '127.0.0.1',
            '::1',
            '172.25.128.0/21',
        ];

        $ip = $request->ip();

        if (!$ip || !IpUtils::checkIp($ip, $allowed)) {
            abort(403, '내부 네트워크 사용자만 접속 가능합니다.');
        }

        return $next($request);
    }
}
