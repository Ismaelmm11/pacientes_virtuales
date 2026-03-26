<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Subject;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CollaboratorInvitedNew extends Mailable
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
            subject: 'Tu colega te ha invitado a colaborar en Pacientes Virtuales',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.collaborator-invited-new',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}