<?php

namespace App\Http\Controllers;

use App\Attendee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use JWTAuth;

class AttendeeController extends Controller
{
    private $attendee;

    public function __construct(Attendee $user)
    {
        $this->attendee = $user;
        Config::set( 'auth.providers.users.model', Attendee::class );
    }

    public function register(Request $request)
    {
        if ($request->get('signup_code')) {
            $found = Attendee::where('signup_code', '=', $request->get('signup_code'));

            if (!$request->get('firstname') || !$request->get('lastname')
                || !$request->get('email') || !$request->get('phone') || !$request->get('password')) {
                return response()->json([
                    'message' => 'Invalid form data',
                ], 400);
            }

            if ($found->first() == null)
                return response()->json([
                    'message' => 'Email not found',
                ], 400);

            $user = $found->first();
            $tmp_dict = $request->all();
            $tmp_dict['email'] = $user->email;
            $tmp_dict['password'] = Hash::make($tmp_dict['password']);

            $user->update($tmp_dict);
            $user->update([
                'signup_code' => ''
            ]);

            $cred = array(
                'email'=> $user->email,
                'password'=> $tmp_dict['password']
            );
            $token = $this->getToken($cred);

            return response()->json([
                'message' => 'Attendee created successfully',
                'data' => $user,
                'token' => $token,
            ], 200);
        }
        else {
            $found = Attendee::where('email', '=', $request->get('email'));

            if ($found->first() != null)
                return response()->json([
                    'message' => 'Duplicated email',
                ], 400);

            if (!$request->get('firstname') || !$request->get('lastname')
                || !$request->get('email') || !$request->get('phone') || !$request->get('password')) {
                return response()->json([
                    'message' => 'Invalid form data',
                ], 400);
            }

            $user = $this->attendee->create([
                'firstname' => $request->get('firstname'),
                'lastname' => $request->get('lastname'),
                'email' => $request->get('email'),
                'phone' => $request->get('phone'),
                'password' => Hash::make($request->get('password')),
                'signup_code' => ''
            ]);

            $cred = $request->only('email', 'password');
            $token = $this->getToken($cred);

            return response()->json([
                'message' => 'Attendee created successfully',
                'data' => $user,
                'token' => $token,
            ], 200);
        }
    }

    public function updateInfo(Request $request)
    {
        $token = JWTAuth::parseToken();
        $id = $token->getPayload()->get('sub');
        $user_type = $token->getPayload()->get('user_type');

        if (!$id || $user_type != 'Attendee') {
            return response()->json([
                'message' => 'invalid token',
            ], 422);
        }

        $user = $token->user();
        if (!$user) {
            return response()->json([
                'message' => 'invalid user',
            ], 422);
        }

        $user->update($request->all());

        return response()->json([
            'message'=> 'Attendee updated successfully',
            'data'=>$user,
        ], 200);
    }

    private function getToken(Array $credentials)
    {
        $token = null;
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'message' => 'invalid_email_or_password',
                ], 422);
            }
        } catch (JWTAuthException $e) {
            return response()->json([
                'message' => 'failed_to_create_token',
            ], 500);
        }
        $token = auth()->claims(['user_type' => 'Attendee',])->attempt($credentials);
        return $token;
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $token = null;
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['invalid_email_or_password'], 422);
            }
        } catch (JWTAuthException $e) {
            return response()->json(['failed_to_create_token'], 500);
        }
        $token = auth()->claims(['user_type' => 'Attendee',])->attempt($credentials);
        return response()->json(compact('token'));
    }

    public function getCurrentInfo(Request $request)
    {
        $token = JWTAuth::parseToken();
        $id = $token->getPayload()->get('sub');
        $user_type = $token->getPayload()->get('user_type');

        if (!$id || $user_type != 'Attendee') {
            return response()->json([
                'message' => 'invalid_token',
                ], 422);
        }
        return response()->json(['result' => $token->user()]);
    }
}
