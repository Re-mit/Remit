<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\IpUtils;

class CheckInternalIp
{
    /**
     * Optionally restrict web access to specific IP ranges.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!config('deployment.restrict_by_ip', false)) {
            return $next($request);
        }

        $allowed = config('deployment.allowed_ip_ranges', []);
        $ip = $request->ip();

        if (!$ip || !IpUtils::checkIp($ip, $allowed)) {
            abort(403, 'This service is only available from approved networks.');
        }

        return $next($request);
    }
}
