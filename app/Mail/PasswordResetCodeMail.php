<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class PasswordResetCodeMail extends Mailable
{
    public function __construct(public string $code) {}

    public function build(): self
    {
        return $this->subject('MushFungAI password reset code')->text('emails.password-reset-code');
    }
}
