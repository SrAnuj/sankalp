<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FirebaseAppleAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class AppleAuthController extends Controller
{
    /**
     * Mobile app Apple login.
     * Flutter sends the Firebase ID token, we verify it and return a Passport token.
     */
    public function login(
        Request $request,
        FirebaseAppleAuthService $apple
    ): JsonResponse {
        $validated = $request->validate([
            'id_token' => ['required', 'string', 'max:10000'],
        ]);

        try {
            $identity = $apple->verify($validated['id_token']);

            $user = DB::transaction(function () use ($identity) {
                $user = User::query()
                    ->where('apple_uid', $identity['uid'])
                    ->first();

                // Apple may hide the email, so only link by email when it is verified.
                if (!$user && $identity['email'] && $identity['email_verified']) {
                    $user = User::query()
                        ->whereRaw('LOWER(email) = ?', [$identity['email']])
                        ->first();
                }

                if ($user) {
                    if ($user->status !== null && (int) $user->status === 0) {
                        throw new \RuntimeException('This account is disabled.');
                    }

                    if (empty($user->apple_uid)) {
                        $user->apple_uid = $identity['uid'];
                    }

                    // Apple sends the name only the first time.
                    if (empty($user->name) && !empty($identity['name'])) {
                        $user->name = mb_substr($identity['name'], 0, 255);
                    }

                    $user->save();

                    return $user;
                }

                $user = new User();
                $user->forceFill([
                    'name'              => $identity['name']
                        ? mb_substr($identity['name'], 0, 255)
                        : null,
                    'email'             => $identity['email'],
                    'email_verified_at' => $identity['email_verified'] ? now() : null,
                    'apple_uid'         => $identity['uid'],
                    'user_type'         => 'User',
                    'status'            => 1,
                ])->save();

                $user->assignRole('User');

                return $user;
            });

            $token = $user->createToken('naamvriddhi-mobile');

            return response()->json([
                'success' => true,
                'message' => 'Login successful.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'phone' => $user->phone,
                        'email' => $user->email,
                    ],
                    'access_token' => $token->accessToken,
                    'token_type' => 'Bearer',
                ],
            ]);
        } catch (Throwable $exception) {
            Log::warning('Digital Naam lekhan Apple login failed', [
                'exception' => get_class($exception),
                'message'   => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to verify login. Please try again.',
            ], 401);
        }
    }
}