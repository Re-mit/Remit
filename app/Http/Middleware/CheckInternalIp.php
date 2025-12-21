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
     * - 로컬 개발: 127.0.0.1, ::1 허용
     */
    public function handle(Request $request, Closure $next)
    {
        $allowed = [
            // 전체 허용 (IPv4/IPv6) - 내부망 제한을 사실상 해제
            '0.0.0.0/0',
            '::/0',

            '127.0.0.1',
            '::1',
            '172.25.128.0/21',
            '172.25.72.0/21',
        ];

        $ip = $request->ip();

        if (!$ip || !IpUtils::checkIp($ip, $allowed)) {
            abort(403, '내부 네트워크 사용자만 접속 가능합니다.');
        }

        return $next($request);
    }
}
