<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends BaseResetPassword
{
    /**
     * Get the reset password notification mail message.
     */
    public function toMail($notifiable): MailMessage
    {
        $url = $this->resetUrl($notifiable);
        $expire = (int) config('auth.passwords.users.expire', 60);
        $appName = config('app.name');

        return (new MailMessage)
            ->subject("{$appName} 비밀번호 재설정")
            ->greeting('비밀번호 재설정 안내')
            ->line('아래 버튼을 눌러 비밀번호를 재설정할 수 있습니다.')
            ->action('비밀번호 재설정', $url)
            ->line("이 링크는 {$expire}분 후 만료됩니다.")
            ->line('본인이 요청하지 않았다면 이 메일을 무시해주세요.');
    }

    protected function resetUrl($notifiable): string
    {
        // 기본 ResetPassword 노티피케이션의 URL 생성 로직을 그대로 사용
        return url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));
    }
}




