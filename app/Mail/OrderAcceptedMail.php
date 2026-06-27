<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderAcceptedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Porudžbina prihvaćena — Rispetto #'.$this->order->order_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.order-accepted',
            with: [
                'order' => $this->order,
                'trackingUrl' => route('order.tracking', $this->order->tracking_token),
            ],
        );
    }
}
