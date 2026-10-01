<?php

namespace App\Mail;

use App\Models\AccessRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccessRequestApproved extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public AccessRequest $accessRequest)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Tu solicitud de acceso a {$this->accessRequest->company->name} ha sido aprobada",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.access-requests.approved',
            with: [
                'name' => $this->accessRequest->name,
                'companyName' => $this->accessRequest->company->name,
                'registerUrl' => route('register', [
                    'email' => $this->accessRequest->email,
                    'company' => $this->accessRequest->company->slug,
                ]),
            ],
        );
    }
}