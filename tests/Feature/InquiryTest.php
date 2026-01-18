<?php

namespace Tests\Feature;

use App\Mail\InquirySubmitted;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InquiryTest extends TestCase
{
    public function test_user_can_submit_inquiry_and_mail_is_sent_to_admin(): void
    {
        Mail::fake();

        config()->set('contact.admin_email', 'admin@example.com');

        // 이 프로젝트는 phpunit.xml에서 sqlite(in-memory)를 기본으로 쓰지만,
        // Windows/PHP 환경에 따라 pdo_sqlite가 없는 경우가 있어 DB 의존 없이 테스트한다.
        // (문의하기는 DB 저장이 없고, Mail 발송 여부만 검증하면 충분함)
        $user = new User([
            'name' => '홍길동',
            'email' => 'hong@gachon.ac.kr',
        ]);
        $user->id = 1;
        $user->exists = true;

        $res = $this->actingAs($user)->post(route('mypage.inquiry.store'), [
            'title' => '문의 제목',
            'content' => '문의 내용입니다.',
        ]);

        $res->assertRedirect(route('mypage.index'));
        $res->assertSessionHas('success');

        Mail::assertSent(InquirySubmitted::class, function (InquirySubmitted $mail) use ($user) {
            return $mail->hasTo('admin@example.com')
                && $mail->user->is($user)
                && $mail->title === '문의 제목';
        });
    }
}


