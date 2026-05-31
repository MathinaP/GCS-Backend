<?php

namespace App\Mail;

use App\Models\ServiceReport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ServiceReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ServiceReport $report,
        private string $pdfContent,
        private string $filename,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Service Report — ' . $this->report->report_number,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mail.service_report');
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfContent, $this->filename)
                ->withMime('application/pdf'),
        ];
    }
}
