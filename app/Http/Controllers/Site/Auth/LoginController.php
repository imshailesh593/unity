<?php

namespace App\Http\Controllers\Site\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FirebaseAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;

class LoginController extends Controller
{
    public function __construct(private readonly FirebaseAuthService $firebaseAuth) {}

    public function show(Request $request)
    {
        return view('site.auth.login', [
            'referralCode' => $request->query('ref'),
        ]);
    }

    /**
     * Trades a client-verified Firebase ID token for a website session.
     * Re-verifies the token server-side rather than trusting the client.
     */
    public function verify(Request $request)
    {
        $request->validate(['id_token' => ['required', 'string']]);

        try {
            $identity = $this->firebaseAuth->verify($request->string('id_token'));
        } catch (FailedToVerifyToken) {
            return response()->json(['message' => 'Invalid or expired OTP token.'], 401);
        }

        $user = User::query()->where('firebase_uid', $identity['uid'])->first();

        if (! $user) {
            return response()->json(['status' => 'registration_required']);
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return response()->json(['status' => 'authenticated', 'redirect' => route('dashboard')]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
