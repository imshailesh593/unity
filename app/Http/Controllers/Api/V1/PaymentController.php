<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\InitiatePaymentRequest;
use App\Services\ActivationService;

class PaymentController extends Controller
{
    public function __construct(private readonly ActivationService $activation) {}

    /**
     * Creates a pending Payment row and a matching gateway order. The
     * gateway webhook — not this endpoint — is what marks it successful.
     */
    public function initiate(InitiatePaymentRequest $request)
    {
        $user = $request->user();

        if ($user->has_paid) {
            return response()->json(['message' => 'Activation fee already paid.'], 409);
        }

        return response()->json($this->activation->initiatePayment($user), 201);
    }
}
