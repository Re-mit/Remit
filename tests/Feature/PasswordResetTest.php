<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        // RefreshDatabase가 DB 연결을 시도하기 전에, 드라이버 유무를 먼저 확인해야 함
        if (!extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('pdo_sqlite 드라이버가 없어 DB 기반 Password Broker 테스트를 스킵합니다.');
        }

        parent::setUp();
    }

    public function test_forgot_password_sends_notification_if_user_exists(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'hong@gachon.ac.kr',
        ]);

        $res = $this->post(route('password.email'), [
            'email' => 'hong@gachon.ac.kr',
        ]);

        $res->assertSessionHas('status', '입력하신 이메일로 비밀번호 재설정 링크를 보냈습니다.');

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'hong@gachon.ac.kr',
            'password' => Hash::make('oldpassword123'),
        ]);

        $token = Password::broker()->createToken($user);

        $res = $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'hong@gachon.ac.kr',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $res->assertRedirect(route('login'));
        $res->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }
}


