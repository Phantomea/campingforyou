<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingNewGarageMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Booking $booking,
        public bool $autoConfirmed = false,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->autoConfirmed
            ? 'Nová rezervácia karavanu (automaticky potvrdená) – CampingForYou'
            : 'Nová rezervácia karavanu čaká na schválenie – CampingForYou';

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.booking.new_garage',
        );
    }
}
