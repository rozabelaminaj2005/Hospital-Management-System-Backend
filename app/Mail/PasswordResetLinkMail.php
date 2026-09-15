<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $email, public string $token)
    {
    }

    public function build(): self
    {
        return $this->subject('Reset your MediCore password')
            ->view('emails.password-reset-link')
            ->with([
                'email' => $this->email,
                'token' => $this->token,
            ]);
    }
}
