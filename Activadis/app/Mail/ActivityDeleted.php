<?php

namespace App\Mail;

use App\Models\Activity;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ActivityDeleted extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Externen hebben geen account, dus voor hen verwijst de knop naar het openbare overzicht.
     */
    public function __construct(public Activity $activity, public bool $isExternal = false) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Activiteit geannuleerd: '.($this->activity->title ?? 'Naamloos'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.activity-deleted',
        );
    }
}
