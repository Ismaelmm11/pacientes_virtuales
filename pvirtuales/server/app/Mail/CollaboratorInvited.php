<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Subject;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CollaboratorInvited extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $collaborator,
        public User $teacher,
        public Subject $asignatura,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Te han invitado a colaborar — Pacientes Virtuales',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.collaborator-invited',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}