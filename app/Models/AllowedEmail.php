<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AllowedEmail extends Model
{
    protected $fillable = [
        'email',
        'memo',
    ];

    public static function normalize(string $email): string
    {
        return mb_strtolower(trim($email));
    }

    /**
     * allowlist가 비어있으면(부트스트랩) 허용, 아니면 등록된 이메일만 허용
     */
    public static function isAllowed(string $email): bool
    {
        $email = self::normalize($email);

        $adminEmail = config('admin.email');
        if ($adminEmail && self::normalize($adminEmail) === $email) {
            return true;
        }

        // allowlist가 비어있으면 기본은 "차단" (운영 정책: 관리자가 등록한 이메일만 회원가입 허용)
        // 단, ENV로 지정된 슈퍼관리자 이메일은 예외로 허용(위에서 처리)
        if (!self::query()->exists()) {
            return false;
        }

        return self::query()->where('email', $email)->exists();
    }
}


