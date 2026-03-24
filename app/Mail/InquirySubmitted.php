<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class InquirySubmitted extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var \App\Models\User
     */
    public $user;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $content;

    /**
     * @var \Illuminate\Support\Carbon
     */
    public $submittedAt;

    /**
     * @var string|null
     */
    public $appName;

    /**
     * @var string|null
     */
    public $appUrl;

    /**
     * @var string|null
     */
    public $env;

    /**
     * @param  \App\Models\User  $user
     * @param  string  $title
     * @param  string  $content
     * @param  \Illuminate\Support\Carbon  $submittedAt
     * @param  string|null  $appName
     * @param  string|null  $appUrl
     * @param  string|null  $env
     * @return void
     */
    public function __construct(User $user, $title, $content, Carbon $submittedAt, $appName = null, $appUrl = null, $env = null)
    {
        $this->user = $user;
        $this->title = $title;
        $this->content = $content;
        $this->submittedAt = $submittedAt;
        $this->appName = $appName;
        $this->appUrl = $appUrl;
        $this->env = $env;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subjectApp = $this->appName ?: 'Remit';
        $subjectTitle = mb_strimwidth($this->title, 0, 80, '...', 'UTF-8');

        return $this->subject("[문의] {$subjectTitle} - {$subjectApp}")
            ->view('emails.inquiry-submitted');
    }
}
