<?php

return [
    'default_seat_count' => env('DEFAULT_SEAT_COUNT', 6),
    'max_seat_count' => 6,

    // 예약/예약취소 내역 보관 기간(개월)
    // 예: 1이면 "최근 1달치만 유지" (cutoff = now - 1 month)
    'retention_months' => (int) env('RESERVATION_RETENTION_MONTHS', 1),
];


