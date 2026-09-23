<?php

namespace App\Listeners;

use App\Events\PaymentSucceeded;
use App\Services\NotificationDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendPaymentReceipt implements ShouldQueue
{
    public function __construct(private readonly NotificationDispatcher $notifications) {}

    public function handle(PaymentSucceeded $event): void
    {
        $payment = $event->payment;

        $this->notifications->notify(
            user: $payment->user,
            type: 'payment_success',
            title: 'Payment received',
            body: "We've received your payment of ₹{$payment->amount}.",
            smsTemplateId: config('services.msg91.templates.payment_receipt'),
            smsVariables: ['amount' => $payment->amount],
        );
    }
}
