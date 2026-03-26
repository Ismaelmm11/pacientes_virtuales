<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Subject;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StudentInvited extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $teacher,
        public Subject $asignatura,
        public string $token
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu profesor te ha invitado a Pacientes Virtuales',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.student-invited',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}