<?php

namespace App\Http\Controllers;

use App\Attendee;
use App\Event;
use App\Location;
use App\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
use App\Jobs\SendEmailJob;

class ReservationController extends Controller
{
    public function reservePublicEvent(Request $request, $event_id)
    {
        $token = JWTAuth::parseToken();
        $id = $token->getPayload()->get('sub');
        $user_type = $token->getPayload()->get('user_type');

        if (!$id || $user_type != 'Attendee') {
            return response()->json([
                'message' => 'invalid_token',
            ], 400);
        }

        $event = Event::where('id', '=', $event_id)->first();
        if ($event == null)
            return response()->json([
                'message' => 'Event not found',
            ], 400);

        if ( time() > $event->end_date )
            return response()->json([
                'message' => 'Expired event',
            ], 400);

        $reservations = $event->attendees;
        if ( sizeof($reservations) == $event->capacity ) {
            return response()->json([
                'message' => 'Out of slot',
            ], 400);
        }

        $reservation = Reservation::create([
            'status' => 'INVITED',
            'event_id' => $event->id,
            'attendee_id' => $id
        ]);

        return response()->json(['result' => $reservation], 200);
    }

    public function reservePrivateEvent(Request $request, $event_id)
    {
        $token = JWTAuth::parseToken();
        $user_id = $token->getPayload()->get('sub');
        $user_type = $token->getPayload()->get('user_type');

        if (!$user_id || $user_type != 'Organizer') {
            return response()->json([
                'message' => 'invalid_token',
            ], 400);
        }

        $event = Event::where('id', '=', $event_id)->first();
        if ($event == null)
            return response()->json([
                'message'=> 'Event not found',
            ], 400);

        if ( time() > $event->end_date )
            return response()->json([
                'message' => 'Expired event',
            ], 400);

        if ($user_id != $event->owner_id)
            return response()->json([
                'message'=> 'Not belongs to owner',
            ], 400);

        $validator = Validator::make(
            [
                'file'      => $request->file('csv_file'),
                'extension' => strtolower($request->file('csv_file')->getClientOriginalExtension()),
            ],
            [
                'file'          => 'required',
                'extension'      => 'required|in:csv',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message'=> 'Validation failed',
            ], 400);
        }
        else {
            $filename = $request->file('csv_file')->getRealPath();
            $data = Excel::load($filename)->get();
            $result = array();
            if (sizeof($data->all()) != $request->get('capacity'))
                return response()->json([
                    'message'=> 'Too many invitations',
                ], 400);
            foreach ($data->all() as $value) {
                $new_email = $value->first();

                $existing_user = Attendee::where('email', '=', $new_email)->first();
                if ($existing_user != null) {
                    $existing_re = Reservation::where('event_id', '=', $event->id)
                        ->where('attendee_id', '=', $existing_user->id)
                        ->first();

                    if ($existing_re == null) {
                        $reservation = Reservation::create([
                            'status' => 'PENDING',
                            'event_id' => $event->id,
                            'attendee_id' => $existing_user->id,
                        ]);
                    } else {
                        $reservation = $existing_re;
                    }
                }
                else {
                    $rand_str = Str::random(32);
                    $new_user = Attendee::create([
                        'name' => '',
                        'username' => '',
                        'email' => $new_email,
                        'phone' => '',
                        'password' => '',
                        'signup_code' => $rand_str
                    ]);

                    $reservation = Reservation::create([
                        'status' => 'PENDING',
                        'event_id' => $event->id,
                        'attendee_id' => $new_user->id,
                    ]);

                    $details = array(
                        "email" => $new_email,
                        "code" => $new_user->signup_code
                    );
                    dispatch(new SendEmailJob($details));
                }
                $result[] = $reservation;
            }

            return response()->json(['result' => $result], 200);
        }
    }

    public function handleEvent(Request $request, $event_id)
    {
        $event = Event::where('id', '=', $event_id)->first();
        if ($event == null)
            return response()->json([
                'message'=> 'Event not found'
            ], 404);

        if ($event->type == 'public')
            return $this->reservePublicEvent($request, $event_id);
        else
            return $this->reservePrivateEvent($request, $event_id);

    }

    public function confirmEvent(Request $request, $event_id)
    {
        $token = JWTAuth::parseToken();
        $id = $token->getPayload()->get('sub');
        $user_type = $token->getPayload()->get('user_type');

        if (!$id || $user_type != 'Attendee') {
            return response()->json([
                'message' => 'invalid_token',
            ], 400);
        }

        $event = Event::where('id', '=', $event_id)->first();
        if ($event == null)
            return response()->json([
                'message' => 'Event not found',
            ], 400);

        if ( time() > $event->end_date )
            return response()->json([
                'message' => 'Expired event',
            ], 400);

        $reservation = Reservation::where('event_id', '=', $event_id)
                    ->where('attend_id', '=', $id)
                    ->where('status', '=', 'PENDING')
                    ->first();
        if ($reservation == null)
            return response()->json([
                'message' => 'Reservation not found',
            ], 400);

        $existing_slots = Reservation::where('event_id', '=', $event_id)
                        ->where('status', '=', 'INVITED')
                        ->get();
        $location = Location::where('id', '=', $event->location_id)->first();

        if (count($existing_slots) == $location->capacity)
            return response()->json([
                'message' => 'Full slot',
            ], 400);

        $reservation->update([
            'status' => 'INVITED'
        ]);

        return response()->json([
            'message' => 'Confirmed',
        ], 200);
    }

    public function removeReservation(Request $request, $event_id)
    {
        $token = JWTAuth::parseToken();
        $id = $token->getPayload()->get('sub');
        $user_type = $token->getPayload()->get('user_type');

        if (!$id || $user_type != 'Attendee') {
            return response()->json([
                'message' => 'invalid_token',
            ], 422);
        }

        $event = Event::where('id', '=', $event_id)->first();
        if ($event == null) {
            return response()->json([
                'message' => 'Event not found',
            ], 404);
        }

        if ($user_type == 'Attendee' && $event->type == 'private') {
            $found = false;
            foreach ($event->attendees as $at) {
                if ($at->id == $id) {
                    $found = true;
                    break;
                }
            }
            if ($found == false) {
                return response()->json([
                    'message' => 'Permission denied',
                ], 400);
            }
        }

        $reserve = Reservation::where('event_id', '=', $event->id)
            ->where('attendee_id', '=', $id)->first();
        $reserve->delete();
        return response()->json([
            'message'=> 'Reservation deleted successfully',
        ], 201);
    }
}
