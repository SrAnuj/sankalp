<?php

namespace App\Services;

use Kreait\Firebase\Contract\Auth;
use RuntimeException;

class FirebaseGoogleAuthService
{
    public function __construct(private Auth $auth)
    {
    }

    /**
     * @return array{email: string, name: ?string}
     */
    public function verify(string $idToken): array
    {
        // 300 = allow up to 5 minutes of clock difference.
        $claims = $this->auth->verifyIdToken($idToken, false, 300)->claims();

        $firebase = $claims->get('firebase');
        $provider = is_array($firebase) ? ($firebase['sign_in_provider'] ?? null) : null;

        if ($provider !== 'google.com') {
            throw new RuntimeException('Token is not a Google sign-in token.');
        }

        $email = $claims->get('email');
        if (!$email || !$claims->get('email_verified')) {
            throw new RuntimeException('Google email is missing or not verified.');
        }

        $name = $claims->get('name');

        return [
            'email' => strtolower($email),
            'name' => is_string($name) ? trim($name) : null,
        ];
    }
}