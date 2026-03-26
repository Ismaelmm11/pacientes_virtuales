<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Subject;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StudentEnrolled extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $student,
        public User $teacher,
        public Subject $asignatura
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Te han inscrito en una asignatura — Pacientes Virtuales',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.student-enrolled',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}