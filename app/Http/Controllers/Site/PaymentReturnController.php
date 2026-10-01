<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;

class PaymentReturnController extends Controller
{
    public function __construct(private readonly PaymentGatewayService $gateway) {}

    /**
     * Where PhonePe's hosted checkout page sends the user's browser back to
     * after they complete (or abandon) payment. This is a UX nicety only —
     * the webhook (server-to-server) remains the sole source of truth for
     * actually flipping has_paid, since this redirect can't be trusted on
     * its own (no verified signature travels with it).
     */
    public function show(Request $request)
    {
        $user = $request->user();

        $payment = Payment::query()
            ->where('user_id', $user->id)
            ->where('purpose', 'self_activation')
            ->where('status', 'pending')
            ->latest()
            ->first();

        if (! $payment) {
            return redirect()->route('dashboard');
        }

        $state = $this->gateway->checkOrderStatus($payment->gateway_txn_id);

        return match ($state) {
            'COMPLETED' => redirect()->route('dashboard')->with('payment_status', 'success'),
            'FAILED' => redirect()->route('dashboard')->with('payment_status', 'failed'),
            default => redirect()->route('dashboard')->with('payment_status', 'pending'),
        };
    }
}
