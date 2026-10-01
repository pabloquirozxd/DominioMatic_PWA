<?php

namespace App\Mail;

use App\Models\AccessRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewAccessRequestNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public AccessRequest $accessRequest)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Nueva solicitud de acceso: {$this->accessRequest->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.access-requests.new-request',
            with: [
                'name' => $this->accessRequest->name,
                'email' => $this->accessRequest->email,
                'message' => $this->accessRequest->message,
                'companyName' => $this->accessRequest->company->name,
                'adminUrl' => route('admin.access-requests.index'),
            ],
        );
    }
}