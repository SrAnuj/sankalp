<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
use App\Events\NotificationEvent;
use Config;
use App\Notifications\GreetingMail;
use Notification;
use Illuminate\Notifications\Notifiable;
use App\Notifications\UserNotification;
use App\Http\Resources\UserProfileDetailCollection;
use Illuminate\Support\Facades\Cookie;
use App\Services\FirebasePhoneAuthService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Throwable;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() {
        dd("Hello wordl");
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function login(Request $request){
        $rules = array(
            'username' => 'required',
            'user_type' => 'required',
            'password' => 'required|min:6|max:15',
            'login_type' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        // process the login
        if ($validator->fails()) {
            $result['result'] = $validator->errors();
            foreach ($result['result']->all() as $error) {
                $mesg[] = $error;
                $message = implode(' ', $mesg);
            }
            $result['message'] = $message;
            $result['status'] = 401; $result['success'] = false;

            // return Redirect::to('login')->withErrors($validator);
            return response()->json($result);
        } else {
            $us = User::where($request->login_type, $request->username)->first();

            if (!empty($us))
            {
                // FIX: was `$us->user_type="Seller"` (assignment). Now a comparison.
                if ($us->status != 1 && $us->user_type == "Seller")
                {
                    $validator = "Your Account is not Approved by admin. Please contact admin for more information!";
                    $result['message'] = $validator;
                    $result['status'] = 401; $result['success'] = false;

                    return response()->json($result);
                }
                if ($us->status != '1')
                {
                    $validator = "Your Account is deleted or disabled. Please contact admin for more information!";
                    $result['message'] = $validator;
                    $result['status'] = 401; $result['success'] = false;

                    return response()->json($result);
                }
                if (!empty($us->email_verified_at))
                {
                    $user_id = $us->id;

                    // FIX: removed `|| ($us && $request->user_type=='user')`.
                    // That condition let anyone log in without the password.
                    if ($us && Hash::check(request('password'), $us->password)) {

                        $user = User::findorFail($us->id);

                        $user->avatar = asset('storage/' . $user->avatar);

                        // FIX: create the token once (was created twice).
                        $token = $user->createToken('sankalp')->accessToken;

                        $result['user'] = $user;
                        $result['token'] = $token;
                        $result['user_id'] =  $user_id;
                        $result['status'] = 200; $result['success'] = true;

                        $result['proceed_otp'] = 'No';
                        $result['message'] = 'login_successfully';

                        if ($user->hasRole('super admin')) {
                            $result['dashboard'] = '/admin/dashboard';
                        } else {
                            $result['dashboard'] = '/';
                        }

                        // Also return roles for frontend use
                        $result['roles'] = $user->getRoleNames();
                        $result['permissions'] = $user->getAllPermissions()->pluck('name');

                        return response()->json($result)
                            ->cookie(
                                'auth_token',
                                $token,
                                1440,
                                '/',
                                '.premierwebtechservices.com',
                                true,   // secure REQUIRED
                                true,   // httpOnly
                                false,
                                'None'  // REQUIRED for cross-site
                            );
                    }
                    else
                    {
                        $validator = "Invalid Password!";
                        $result['message'] = $validator;
                        $result['status'] = 401; $result['success'] = false;

                        $result['proceed_otp'] = 'No';
                        return response()->json($result);
                    }
                }
                else
                {
                    $validator = "Please verify your account to login!";
                    $result['message'] = $validator;
                    $result['user_id'] = $us->id;
                    $result['user'] = $us;
                    $result['proceed_otp'] = 'Yes';
                    $result['status'] = 401;
                    $result['success'] = false;

                    return response()->json($result);
                }
            }
            else
            {
                $validator = "This Email/Phone is not registered with us!";
                $result['message'] = $validator;
                $result['proceed_otp'] = 'No';
                $result['status'] = 401; $result['success'] = false;

                return response()->json($result);
            }
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

         $page = $request->page;
         $custom_validation_messages = [
         'name.regex' => "Name can contain only letters. ",

      ];
            $rules = array(
                'user_type' => 'nullable',
                'login_type' => 'nullable',
                'name' => 'bail|required|min:3|regex:/^[A-Za-z\s\-]+$/',  //|regex:/^[A-Za-z\s\-]+$/',
                'email' => 'bail|required_if:login_type,==,email|email|unique:users,email',
                'phone' => 'bail|required_if:login_type,==,phone|max:15|min:10|unique:users,phone',
                'password' => 'required_if:user_type,==,business|max:15|min:6',
                'confirm_password' => 'required_if:user_type,==,business|max:15|min:6|same:password',
                'fcm_token' => 'nullable',
                'fcm_uid' => 'nullable',
            );


        $validator = Validator::make($request->all(), $rules, $custom_validation_messages);

        // process the login

        if ($validator->fails()) {
            $result['result'] = $validator->errors();
            foreach ($result['result']->all() as $error) {
                $mesg[] = $error;
                $message = implode(' ', $mesg);
            }
            $result['message'] = $message;
            $result['status'] = 401; $result['success'] = false;

            // return Redirect::to('login')->withErrors($validator);
            return response()->json($result);

        }
        $otp = rand(100000,999999);
        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->password =  Hash::make($request->password);
        //$user->provider = $request->login_type;
        $user->user_type = 'User';
        $user->status = true;
        //$user->current_team_id = '1';
        $user->email_verified_at = now();
        $user->save();

        function random_strings($length_of_string)
        {
            $str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
            return substr(str_shuffle($str_result), 0, $length_of_string);
        }
        //$user->refferal_code = random_strings(10).'_'.$user->id;
        $user->save();

        $user->assignRole('User');

        $tp=empty($request->phone) ? $request->email : $request->phone;
         $admin = User::whereHas('roles', function ($query){
        $query->where('name','=','super admin');
        })->first();
        $details = [
            'body' => 'A New User created account by : ' . $tp,
            'head' => 'User Register',
            'request_id' => $user->id,
            'user_id' => $admin->id,
            'type' => 'App\Notifications\User',
            'notifiable_type' => 'App\Models\User',
            'class' => 'feather icon-user-plus font-medium-5 warning',
            'color' => 'info media-heading',
            'url' => 'admin/users',
            'sender_id' => $user->id,
        ];
        $admins = User::where('user_type','Admin')->get();
         Notification::send($admins, new UserNotification($details));
        event(new NotificationEvent($details));

        if($request->login_type=='email' || $request->login_type=='phone') {
                $url=Config::get('app.url');
                $data['logo']=$url."public/images/logo/logo.png";

                $data['email'] = $user->email;
                $data['name'] = $user->name;
                $email = $user->email;

                  //$user->sendEmailVerificationNotification();
                //Mail::to($email)->send(new VerificationEmail($data));
                $user->notify(new GreetingMail());


        $result['message'] = 'Your Account is created successfully. Please verify your email id by visiting your registered email.';
        } else {
        $result['message'] = 'Your Account is created successfully. Please login.';
        }

        $result['status'] = 200; $result['success'] = true;
        $result['user'] = $user;
        $result['token'] = $user->createToken('tyka')->accessToken;
        $result['user_id'] = $user->id;

        return response()->json($result);

    }

    /**
     * Mobile app phone login.
     * Flutter sends the Firebase ID token, we verify it and return a Passport token.
     */
    public function loginWithOtp(
        Request $request,
        FirebasePhoneAuthService $firebase
    ): JsonResponse {
        $validated = $request->validate([
            'id_token' => ['required', 'string', 'max:10000'],
        ]);

        try {
            $identity = $firebase->verify($validated['id_token']);

            $phone    = $identity['phone'];                         // +919876543210
            $national = substr(preg_replace('/\D/', '', $phone), -10); // 9876543210

            $user = DB::transaction(function () use ($identity, $phone, $national) {
                $user = User::query()
                    ->where('firebase_uid', $identity['uid'])
                    ->first();

                if (!$user) {
                    // Match both "+91..." and plain 10 digit numbers saved by the web app.
                    $user = User::query()
                        ->whereIn('phone', [$phone, $national])
                        ->first();
                }

                if ($user) {
                    if (
                        $user->firebase_uid &&
                        $user->firebase_uid !== $identity['uid']
                    ) {
                        throw new \RuntimeException(
                            'This phone number is linked to another account.'
                        );
                    }

                    if ($user->status !== null && (int) $user->status === 0) {
                        throw new \RuntimeException('This account is disabled.');
                    }

                    $user->firebase_uid = $identity['uid'];

                    // Keep the phone format already stored for this user.
                    if (empty($user->phone)) {
                        $user->phone = $phone;
                    }

                    $user->save();

                    return $user;
                }

                // First login: create the account. Name is asked later in the app.
                $user = new User();
                $user->forceFill([
                    'name'         => null,
                    'phone'        => $phone,
                    'firebase_uid' => $identity['uid'],
                    'user_type'    => 'User',
                    'status'       => 1,
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
                    ],
                    'access_token' => $token->accessToken,
                    'token_type' => 'Bearer',
                ],
            ]);
        } catch (Throwable $exception) {
            Log::warning('Digital Naam lekhan OTP login failed', [
                'exception' => get_class($exception),
                'message'   => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to verify login. Please try again.',
            ], 401);
        }
    }

    /**
     * Mobile app: save the user's name (Name screen after first login).
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $validated = $request->validate([
            // Letters from any language (Hindi, Punjabi, English), spaces, dots, hyphens.
            'name' => ['required', 'string', 'min:2', 'max:40', 'regex:/^[\p{L}\p{M}\s.\-]+$/u'],
        ]);

        $user = $request->user();
        $user->name = trim($validated['name']);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->phone,
                ],
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->token()->revoke();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id) {}

}