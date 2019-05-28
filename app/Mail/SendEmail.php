<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendEmail extends Mailable
{
    use Queueable, SerializesModels;

    private $signup_code;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($signup_code)
    {
        $this->signup_code = $signup_code;
        $this->url_mail = env('URL_MAIL');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $data = [
            'signup_code', $this->signup_code,
            'url_mail', $this->url_mail
        ];

        return $this->view('emails.invitation')->with('signup_code', $this->signup_code)
            ->with('url_mail', $this->url_mail);
    }
}
