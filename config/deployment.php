<?php

return [
    'force_https' => (bool) env('APP_FORCE_HTTPS', false),

    'restrict_by_ip' => (bool) env('INTERNAL_IP_FILTER_ENABLED', false),

    'allowed_ip_ranges' => array_values(array_filter(array_map(
        static fn (string $range): string => trim($range),
        explode(',', (string) env('INTERNAL_IP_ALLOWED_CIDRS', '127.0.0.1,::1'))
    ))),
];
