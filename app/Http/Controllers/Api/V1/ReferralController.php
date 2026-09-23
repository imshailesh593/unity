<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ActivationService;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    public function __construct(private readonly ActivationService $activation) {}

    public function index(Request $request)
    {
        $user = $request->user();

        $referred = $user->referredUsers()
            ->select('id', 'name', 'phone', 'status', 'has_paid', 'created_at')
            ->latest()
            ->get();

        return response()->json([
            'referral_code' => $user->referral_code,
            'referral_link' => url('/r/'.$user->referral_code),
            'referrals_required' => $this->activation->requiredReferrals(),
            'referrals_paid' => $this->activation->paidReferralsCount($user),
            'referred_count' => $referred->count(),
            'referred' => $referred,
        ]);
    }
}
