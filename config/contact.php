<?php

return [
    // 문의하기 메일 수신자(관리자 이메일)
    // - 우선순위: ADMIN_CONTACT_EMAIL > MAIL_FROM_ADDRESS > null
    'admin_email' => env('ADMIN_CONTACT_EMAIL', env('MAIL_FROM_ADDRESS')),
];




