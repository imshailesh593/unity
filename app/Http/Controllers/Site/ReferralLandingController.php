<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\User;

class ReferralLandingController extends Controller
{
    public function show(string $code)
    {
        $referrer = User::query()->where('referral_code', $code)->first();

        return view('site.referral', compact('referrer', 'code'));
    }
}
