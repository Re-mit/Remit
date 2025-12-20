<!doctype html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>이메일 인증번호</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f6f7fb; padding:24px;">
    <div style="max-width:520px; margin:0 auto; background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; padding:20px;">
        <h2 style="margin:0 0 12px; font-size:18px;">스터디룸룸 예약 시스템 이메일 인증번호</h2>
        <p style="margin:0 0 16px; color:#374151; font-size:14px;">
            아래 인증번호를 회원가입 화면에 입력해주세요. (유효시간: {{ $expiresMinutes }}분)
        </p>
        <div style="font-size:28px; letter-spacing:6px; font-weight:700; color:#111827; padding:14px 16px; border-radius:10px; background:#eff6ff; border:1px solid #bfdbfe; display:inline-block;">
            {{ $code }}
        </div>
        <p style="margin:16px 0 0; color:#6b7280; font-size:12px;">
            본 메일은 발신전용입니다. 본인이 요청하지 않았다면 무시해주세요.
        </p>
    </div>
</body>
</html>


