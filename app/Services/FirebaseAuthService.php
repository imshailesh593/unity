<?php

namespace App\Services;

use Kreait\Firebase\Contract\Auth as FirebaseAuth;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;

class FirebaseAuthService
{
    public function __construct(private readonly FirebaseAuth $auth) {}

    /**
     * Verifies a Firebase Phone Auth ID token server-side and returns the
     * verified identity. Never trust a client-reported phone/uid directly —
     * this is the only source of truth for "who is this user".
     *
     * @return array{uid: string, phone: ?string}
     *
     * @throws FailedToVerifyToken
     */
    public function verify(string $idToken): array
    {
        $verifiedToken = $this->auth->verifyIdToken($idToken);

        return [
            'uid' => (string) $verifiedToken->claims()->get('sub'),
            'phone' => $verifiedToken->claims()->get('phone_number'),
        ];
    }
}
