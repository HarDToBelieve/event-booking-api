<?php

namespace App\Http\Controllers;

use App\Organizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use JWTAuth;

class OrganizerController extends Controller
{
    private $organizer;

    public function __construct(Organizer $user)
    {
        $this->organizer = $user;
        Config::set( 'auth.providers.users.model', Organizer::class );
    }

    public function register(Request $request)
    {
        $found = Organizer::where('email', '=', $request->get('email'));

        if ($found->first() != null)
            return response()->json([
                'message'=> 'Duplicated email',
            ], 422);

        $user = $this->organizer->create([
            'firstname' => $request->get('firstname'),
            'lastname' => $request->get('lastname'),
            'email' => $request->get('email'),
            'phone' => $request->get('phone'),
            'password' => Hash::make($request->get('password'))
        ]);

        return response()->json([
            'status'=> 200,
            'message'=> 'Organizer created successfully',
            'data'=>$user,
        ]);
    }

    public function updateInfo(Request $request)
    {
        $token = JWTAuth::parseToken();
        $id = $token->getPayload()->get('sub');
        $user_type = $token->getPayload()->get('user_type');

        if (!$id || $user_type != 'Organizer') {
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
            'message'=> 'Organizer updated successfully',
            'data'=>$user,
        ], 200);
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
        $token = auth()->claims(['user_type' => 'Organizer',])->attempt($credentials);
        return response()->json(compact('token'));
    }

    public function getCurrentInfo(Request $request)
    {
        $token = JWTAuth::parseToken();
        $id = $token->getPayload()->get('sub');
        $user_type = $token->getPayload()->get('user_type');

        if (!$id || $user_type != 'Organizer') {
            return response()->json([
                'message' => 'invalid_token',
            ], 400);
        }
        return response()->json(['result' => $token->user()]);
    }

    public function listAll(Request $request)
    {
        $list_orgs = Organizer::paginate();
        return response()->json($list_orgs, 200);
    }

    public function getSpecificInfo(Request $request, $id)
    {
        $found = Organizer::where('id', '=', $id);

        if ($found->first() == null)
            return response()->json([
                'message'=> 'Organizer not found',
            ], 400);

        return response()->json(['result' => $found->first()]);
    }
}
