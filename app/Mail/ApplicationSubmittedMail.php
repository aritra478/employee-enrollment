<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class ApplicationSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user, $pdfUrl;

    public function __construct($user, $pdfUrl)
    {
        $this->user = $user;
        $this->pdfUrl = $pdfUrl;
    }

    public function build()
    {
        return $this->subject('Application Submitted Successfully')
            ->markdown('emails.application_submitted');
    }
}
