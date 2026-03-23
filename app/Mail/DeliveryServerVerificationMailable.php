<?php

namespace App\Mail;

use App\Models\DeliveryServer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DeliveryServerVerificationMailable extends Mailable
{
    use Queueable, SerializesModels;

    // Don't queue verification emails - send them immediately
    // Remove ShouldQueue to send synchronously

    /**
     * Create a new message instance.
     */
    public function __construct(
        public DeliveryServer $deliveryServer
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verify Your Delivery Server - ' . $this->deliveryServer->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $routeName = $this->deliveryServer->customer_id ? 'customer.delivery-servers.verify' : 'admin.delivery-servers.verify';

        $verificationUrl = route($routeName, [
            'delivery_server' => $this->deliveryServer->id,
            'token' => $this->deliveryServer->verification_token,
        ]);

        return new Content(
            html: 'emails.delivery-server-verification',
            text: 'emails.delivery-server-verification-text',
            with: [
                'deliveryServer' => $this->deliveryServer,
                'verificationUrl' => $verificationUrl,
            ],
        );
    }
}

