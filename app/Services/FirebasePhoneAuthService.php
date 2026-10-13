<?php

namespace App\Services;

use Kreait\Firebase\Contract\Auth;
use RuntimeException;

class FirebasePhoneAuthService
{
    public function __construct(private Auth $auth)
    {
        
    }

    /**
     * @return array{uid: string, phone: string}
     */
    public function verify(string $idToken): array
    {
        // 300 = allow up to 5 minutes of clock difference between this
        // server and Firebase.
        $verified = $this->auth->verifyIdToken($idToken, false, 300);
        $claims = $verified->claims();

        $uid = $claims->get('sub');
        $phone = $claims->get('phone_number');

        if (!$uid || !$phone) {
            throw new RuntimeException('Firebase token has no phone number.');
        }

        return ['uid' => $uid, 'phone' => $phone];
    }
}