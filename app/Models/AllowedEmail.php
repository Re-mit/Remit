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

        if (!self::query()->exists()) {
            return true;
        }

        return self::query()->where('email', $email)->exists();
    }
}


