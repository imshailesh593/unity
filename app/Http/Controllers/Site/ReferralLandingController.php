<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;

class ReferralLandingController extends Controller
{
    /**
     * The referral program is switched off while payment-gateway approval is
     * pending. Old shared links land on the home page instead of a 404.
     */
    public function show(string $code)
    {
        return redirect()->route('home');
    }
}
