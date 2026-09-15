<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewDoctorAccountMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $email, public string $temporaryPassword)
    {
    }

    public function build(): self
    {
        return $this->subject('Your MediCore doctor account')
            ->view('emails.new-doctor-account')
            ->with([
                'email' => $this->email,
                'temporaryPassword' => $this->temporaryPassword,
            ]);
    }
}
