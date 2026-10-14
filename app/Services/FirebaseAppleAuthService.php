<?php

namespace App\Services;

use Kreait\Firebase\Contract\Auth;
use RuntimeException;

class FirebaseAppleAuthService
{
    public function __construct(private Auth $auth)
    {
    }

    /**
     * @return array{uid: string, email: ?string, email_verified: bool, name: ?string}
     */
    public function verify(string $idToken): array
    {
        // 300 = allow up to 5 minutes of clock difference.
        $claims = $this->auth->verifyIdToken($idToken, false, 300)->claims();

        $firebase = $claims->get('firebase');
        $provider = is_array($firebase) ? ($firebase['sign_in_provider'] ?? null) : null;

        if ($provider !== 'apple.com') {
            throw new RuntimeException('Token is not an Apple sign-in token.');
        }

        $uid = $claims->get('sub');
        if (!$uid) {
            throw new RuntimeException('Token has no user id.');
        }

        $email = $claims->get('email');
        $name = $claims->get('name');

        return [
            'uid' => $uid,
            'email' => is_string($email) && $email !== '' ? strtolower($email) : null,
            'email_verified' => filter_var(
                $claims->get('email_verified'),
                FILTER_VALIDATE_BOOLEAN
            ),
            'name' => is_string($name) && trim($name) !== '' ? trim($name) : null,
        ];
    }
}