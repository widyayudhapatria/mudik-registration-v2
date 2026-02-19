<?php

namespace App\Mail;

use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class SeatAllocationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Registration $registration;
    public Collection $seatAllocations;

    /**
     * Create a new message instance.
     */
    public function __construct(Registration $registration, Collection $seatAllocations)
    {
        $this->registration = $registration;
        $this->seatAllocations = $seatAllocations;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎫 E-Ticket Mudik Gratis 2026 - ' . $this->registration->destination->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.seat-allocation',
            with: [
                'registration' => $this->registration,
                'seatAllocations' => $this->seatAllocations,
                'destination' => $this->registration->destination,
                'email' => $this->registration->formLink->email,
            ],
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
