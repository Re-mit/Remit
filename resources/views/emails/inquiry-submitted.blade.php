<!doctype html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>문의 접수</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f6f7fb; padding:24px;">
    <div style="max-width:640px; margin:0 auto; background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; padding:20px;">
        <h2 style="margin:0 0 12px; font-size:18px; color:#111827;">문의가 접수되었습니다</h2>

        <div style="margin:0 0 16px; font-size:13px; color:#6b7280;">
            <div>APP: {{ $appName ?? '-' }}</div>
            <div>URL: {{ $appUrl ?? '-' }}</div>
            <div>ENV: {{ $env ?? '-' }}</div>
            <div>제출 시각: {{ $submittedAt?->format('Y-m-d H:i:s') }} (Asia/Seoul)</div>
        </div>

        <div style="border-top:1px solid #e5e7eb; padding-top:16px; margin-top:16px;">
            <h3 style="margin:0 0 8px; font-size:14px; color:#111827;">문의자 정보</h3>
            <div style="font-size:13px; color:#374151; line-height:1.6;">
                <div>id: {{ $user->id }}</div>
                <div>name: {{ $user->name }}</div>
                <div>email: {{ $user->email }}</div>
            </div>
        </div>

        <div style="border-top:1px solid #e5e7eb; padding-top:16px; margin-top:16px;">
            <h3 style="margin:0 0 8px; font-size:14px; color:#111827;">제목</h3>
            <div style="font-size:14px; color:#111827; font-weight:700; background:#eff6ff; border:1px solid #bfdbfe; border-radius:10px; padding:12px 14px;">
                {{ $title }}
            </div>
        </div>

        <div style="border-top:1px solid #e5e7eb; padding-top:16px; margin-top:16px;">
            <h3 style="margin:0 0 8px; font-size:14px; color:#111827;">내용</h3>
            <div style="font-size:13px; color:#374151; line-height:1.7; white-space:pre-wrap; background:#f9fafb; border:1px solid #e5e7eb; border-radius:10px; padding:12px 14px;">
                {{ $content }}
            </div>
        </div>

        <p style="margin:16px 0 0; color:#6b7280; font-size:12px;">
            본 메일은 발신전용입니다.
        </p>
    </div>
</body>
</html>


