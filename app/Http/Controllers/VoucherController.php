<?php

namespace App\Http\Controllers;

use App\Event;
use App\Voucher;
use Illuminate\Http\Request;
use JWTAuth;

class VoucherController extends Controller
{
    public function createVoucher(Request $request)
    {
        $token = JWTAuth::parseToken();
        $id = $token->getPayload()->get('sub');
        $user_type = $token->getPayload()->get('user_type');

        if (!$id || $user_type != 'Organizer') {
            return response()->json([
                'message' => 'invalid_token',
            ], 400);
        }

        $found = Voucher::where('code', '=', $request->get('code'));
        if ($found->first() != null)
            return response()->json([
                'message'=> 'Duplicated voucher',
            ], 400);

        $event = Event::where('id', '=', $request->get('event_id'))
                        ->where('owner_id', '=', $id)->first();
        if ($event == null) {
            return response()->json([
                'message'=> 'Event not found',
            ], 400);
        }

        $voucher = Voucher::create([
            'event_id' => $request->get('event_id'),
            'discount_percent' => $request->get('discount_percent'),
            'start_date' => strtotime($request->get('start_date')),
            'end_date' => strtotime($request->get('end_date')),
            'code' => $request->get('code'),
        ]);
        return response()->json(['result' => $voucher], 200);
    }

    public function validVoucher(Request $request)
    {
        $token = JWTAuth::parseToken();
        $id = $token->getPayload()->get('sub');
        $user_type = $token->getPayload()->get('user_type');

        if (!$id || $user_type != 'Attendee') {
            return response()->json([
                'message' => 'invalid_token',
            ], 400);
        }

        $found = Voucher::where('code', '=', $request->get('code'));
        if ($found->first() == null)
            return response()->json([
                'message'=> '',
            ], 400);
    }
}
