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
                 if ($us->status != 1 && $us->user_type="Seller")
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
                     if (($us && Hash::check(request('password'), $us->password)) ||
                     ($us && $request->user_type=='user') ){

                            $user = User::findorFail($us->id); //auth('sanctum')->user();
							
                            //$user->fcm_token=$request->fcm_token;
                            //$user->fcm_uid=$request->fcm_uid;
                            $user->save();
							$user->avatar = asset('storage/' . $user->avatar);
                            //$user->package_expires = $user->end_date<date('Y-m-d') || empty($user->end_date) ? true : false;
							 $token = $user->createToken('tyka')->accessToken;
                            $result['user'] = $user;
                            $result['token'] = $user->createToken('tyka')->accessToken;
                            $result['user_id'] =  $user_id;
                            $result['status'] = 200; $result['success'] = true;

                            $result['proceed_otp'] = 'No';
                            $result['message'] = 'login_successfully';
							
							if ($user->hasRole('super admin')) {
								$result['dashboard'] = '/admin/dashboard';
							} else {
								$result['dashboard'] = '/';
							}

							// ✅ ADDED: Also return roles for frontend use
							$result['roles'] = $user->getRoleNames();
							$result['permissions'] = $user->getAllPermissions()->pluck('name'); 
                            // return response()->json($result);
							
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
