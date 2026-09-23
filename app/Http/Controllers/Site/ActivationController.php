<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\ActivationService;
use Illuminate\Http\Request;

class ActivationController extends Controller
{
    public function __construct(private readonly ActivationService $activation) {}

    /**
     * Web-session equivalent of the mobile API's PaymentController::initiate.
     * Same webhook confirms both, since it keys off the gateway order id.
     */
    public function initiate(Request $request)
    {
        $user = $request->user();

        if ($user->has_paid) {
            return response()->json(['message' => 'Activation fee already paid.'], 409);
        }

        return response()->json($this->activation->initiatePayment($user), 201);
    }
}
