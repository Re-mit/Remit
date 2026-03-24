<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailVerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var string
     */
    public $code;

    /**
     * @var int
     */
    public $expiresMinutes;

    /**
     * @param  string  $code
     * @param  int  $expiresMinutes
     * @return void
     */
    public function __construct($code, $expiresMinutes = 10)
    {
        $this->code = $code;
        $this->expiresMinutes = $expiresMinutes;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Remit 이메일 인증번호')
            ->view('emails.verification-code');
    }
}
