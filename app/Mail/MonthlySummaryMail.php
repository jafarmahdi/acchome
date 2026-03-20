<?php

namespace App\Mail;

use App\Models\Family;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MonthlySummaryMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Family $family,
        public array $summaryData
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Monthly Finance Report - :family', ['family' => $this->family->name]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.monthly-summary',
            with: [
                'family' => $this->family,
                'data' => $this->summaryData,
            ],
        );
    }
}
