<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\VerifyOtpRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\FirebaseAuthService;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;

class OtpController extends Controller
{
    public function __construct(private readonly FirebaseAuthService $firebaseAuth) {}

    /**
     * Verifies a Firebase Phone Auth ID token. If a matching account already
     * exists, logs it in immediately. Otherwise tells the client to continue
     * to /auth/register — no account is created at this step.
     */
    public function verify(VerifyOtpRequest $request)
    {
        try {
            $identity = $this->firebaseAuth->verify($request->string('id_token'));
        } catch (FailedToVerifyToken) {
            return response()->json(['message' => 'Invalid or expired OTP token.'], 401);
        }

        $user = User::query()->where('firebase_uid', $identity['uid'])->first();

        if (! $user) {
            return response()->json([
                'status' => 'registration_required',
            ]);
        }

        $token = $user->createToken('unity-app')->plainTextToken;

        return response()->json([
            'status' => 'authenticated',
            'token' => $token,
            'user' => new UserResource($user),
        ]);
    }
}
