<?php

namespace App\Http\Controllers;

use App\Location;
use App\Organizer;
use Illuminate\Http\Request;
use JWTAuth;

class LocationController extends Controller
{
    public function createLocation(Request $request)
    {
        $token = JWTAuth::parseToken();
        $id = $token->getPayload()->get('sub');
        $user_type = $token->getPayload()->get('user_type');

        if (!$id || $user_type != 'Organizer') {
            return response()->json([
                'message' => 'invalid_token',
            ], 400);
        }
        $found = Location::where('address', '=', $request->get('address'));
        if ($found->first() != null)
            return response()->json([
                'message'=> 'Duplicated location',
            ], 400);

        $location = Location::create([
            'name_location' => $request->get('name_location'),
            'address' => $request->get('address'),
            'capacity' => $request->get('capacity'),
            'owner_id' => $id,
        ]);
        return response()->json(['result' => $location], 200);
    }

    public function updateLocation(Request $request, $id)
    {
        $token = JWTAuth::parseToken();
        $user_id = $token->getPayload()->get('sub');
        $user_type = $token->getPayload()->get('user_type');

        if (!$user_id || $user_type != 'Organizer') {
            return response()->json([
                'message' => 'invalid_token',
            ], 400);
        }

        $found = Location::where('id', '=', $id)->where('owner_id', '=', $user_id);
        if ($found->first() == null)
            return response()->json([
                'message'=> 'Location not found',
            ], 400);

        $location = $found->first();
        $location->update($request->all());
        return response()->json([
            'message'=> 'Location updated successfully',
            'data'=>$location,
        ], 201);
    }

    public function deleteLocation(Request $request, $id)
    {
        $token = JWTAuth::parseToken();
        $user_id = $token->getPayload()->get('sub');
        $user_type = $token->getPayload()->get('user_type');

        if (!$user_id || $user_type != 'Organizer') {
            return response()->json([
                'message' => 'invalid_token',
            ], 400);
        }

        $found = Location::where('id', '=', $id)->where('owner_id', '=', $user_id);
        if ($found->first() == null)
            return response()->json([
                'message'=> 'Location not found',
            ], 400);
        $location = $found->first();
        $location->delete();
        return response()->json([
            'message'=> 'Location deleted successfully',
        ], 201);
    }

    public function listAll(Request $request)
    {
        $list_locs = Location::paginate();
        return response()->json($list_locs, 200);
    }

    public function getInfo(Request $request, $id)
    {
        $found = Location::where('id', '=', $id);
        if ($found->first() == null)
            return response()->json([
                'message'=> 'Location not found',
            ], 400);
        return response()->json(['result' => $found->first()], 200);
    }

    public function getLocationsByOwner(Request $request, $owner_id)
    {
        $owner = Organizer::where('id', '=', $owner_id)->first();
        if ($owner == null)
            return response()->json([
                'message'=> 'Owner not found',
            ], 400);

        $list_loc = Location::where('owner_id', '=', $owner_id)->paginate();
        return response()->json([
            'owner_id' => $owner_id,
            'result' => $list_loc,
        ], 200);
    }
}
