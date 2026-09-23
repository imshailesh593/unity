<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ActivationService;

class SettingController extends Controller
{
    public function public(ActivationService $activation)
    {
        return response()->json([
            'activation_fee' => $activation->activationFee(),
            'required_referrals' => $activation->requiredReferrals(),
        ]);
    }
}
