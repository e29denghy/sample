<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegistrationConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '感谢注册 Sample 应用！请确认你的邮箱。');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.confirm');
    }

    public function attachments(): array
    {
        return [];
    }
}
