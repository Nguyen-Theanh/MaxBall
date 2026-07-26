<?php

namespace App\Mail;

use App\Models\Order;
use App\Support\OrderCancellationReasons;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderCancelledMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $cancellationMessage;

    public function __construct(public Order $order)
    {
        $this->cancellationMessage = OrderCancellationReasons::emailMessage(
            $order->cancellation_reason,
            $order->cancelled_by
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Đơn hàng #'.$this->order->order_code.' đã được hủy - MaxBall',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order_cancelled',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
