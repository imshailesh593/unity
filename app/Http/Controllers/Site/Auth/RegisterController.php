<?php

namespace App\Http\Controllers\Site\Auth;

use App\Events\ReferralJoined;
use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Models\User;
use App\Services\FirebaseAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;

class RegisterController extends Controller
{
    public function __construct(private readonly FirebaseAuthService $firebaseAuth) {}

    /**
     * Completes registration after OTP verification, mirroring the mobile
     * API's RegisterController. Re-verifies the Firebase ID token itself
     * rather than trusting client-supplied phone/uid.
     */
    public function register(Request $request)
    {
        $request->validate([
            'id_token' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'referral_code' => ['nullable', 'string', 'max:12', 'exists:users,referral_code'],
        ]);

        try {
            $identity = $this->firebaseAuth->verify($request->string('id_token'));
        } catch (FailedToVerifyToken) {
            return response()->json(['message' => 'Invalid or expired OTP token.'], 401);
        }

        if (empty($identity['phone'])) {
            return response()->json(['message' => 'Token has no verified phone number.'], 422);
        }

        if (User::query()->where('firebase_uid', $identity['uid'])->exists()) {
            return response()->json(['message' => 'Account already registered.'], 409);
        }

        $referrer = $request->filled('referral_code')
            ? User::query()->where('referral_code', $request->string('referral_code'))->first()
            : null;

        $user = DB::transaction(function () use ($request, $identity, $referrer) {
            $user = User::create([
                'name' => $request->string('name'),
                'phone' => $identity['phone'],
                'email' => $request->input('email'),
                'firebase_uid' => $identity['uid'],
                'referred_by' => $referrer?->id,
                'status' => 'registered',
            ]);

            if ($referrer) {
                Referral::create([
                    'referrer_id' => $referrer->id,
                    'referred_id' => $user->id,
                    'referred_paid' => false,
                ]);
            }

            return $user;
        });

        if ($referrer) {
            event(new ReferralJoined($referrer, $user));
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return response()->json(['status' => 'ok', 'redirect' => route('dashboard')], 201);
    }
}
