<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureNotSuspended
{
    /**
     * 정지 계정은 서비스 이용을 차단한다.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if ($user && !empty($user->suspended_at)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', '계정이 정지되었습니다. 관리자에게 문의하세요.');
        }

        return $next($request);
    }
}


