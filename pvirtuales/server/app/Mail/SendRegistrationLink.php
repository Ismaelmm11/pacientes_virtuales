<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendRegistrationLink extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * El token de invitación.
     */
    public string $token;

    /**
     * Crea una nueva instancia del mensaje.
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * Define el asunto del email.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Completa tu registro en Pacientes Virtuales',
        );
    }

    /**
     * Define la vista que se usará para el contenido del email.
     */
    public function content(): Content
    {
        return new Content(
            // Apunta a 'resources/views/emails/invite.blade.php'
            view: 'emails.invite',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
