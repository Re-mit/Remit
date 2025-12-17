<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\IpUtils;

class CheckInternalIp
{
    /**
     * 사설망(내부망) IP 대역만 접근 허용
     * - 허용 대역: 172.25.128.0/21
     * - 로컬 개발: 127.0.0.1, ::1 허용
     */
    public function handle(Request $request, Closure $next)
    {
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


