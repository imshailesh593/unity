<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\ActivationService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private readonly ActivationService $activation) {}

    public function index(Request $request)
    {
        $user = $request->user();

        return view('site.dashboard', [
            'user' => $user,
            'requiredReferrals' => $this->activation->requiredReferrals(),
            'paidReferrals' => $this->activation->paidReferralsCount($user),
            'activationFee' => $this->activation->activationFee(),
            'referralLink' => route('referral.landing', $user->referral_code),
        ]);
    }
}
