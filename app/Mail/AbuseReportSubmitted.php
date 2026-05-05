<?php

namespace App\Mail;

use App\Models\AbuseReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AbuseReportSubmitted extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public AbuseReport $report
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $replyTo = $this->report->anonymous_report || !$this->report->reporter_email
            ? 'safeguarding@lgihe.ac.ug'
            : $this->report->reporter_email;

        return new Envelope(
            from: new Address('noreply@lgihe.ac.ug', 'LGIHE Safeguarding'),
            replyTo: [new Address($replyTo)],
            subject: "🚨 URGENT: Abuse Report [{$this->report->report_id}] - {$this->report->incident_type}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.abuse-report',
            with: [
                'report' => $this->report,
                'isAnonymous' => $this->report->anonymous_report,
                'incidentTypeDisplay' => $this->report->incident_type_display,
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
