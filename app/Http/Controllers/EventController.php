<?php

namespace App\Http\Controllers;

use App\Attendee;
use App\Event;
use App\Location;
use App\Organizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Nexmo\User\User;

class EventController extends Controller
{
    public function createEvent(Request $request)
    {
        $token = JWTAuth::parseToken();
        $id = $token->getPayload()->get('sub');
        $user_type = $token->getPayload()->get('user_type');

        if (!$id || $user_type != 'Organizer') {
            return response()->json([
                'message' => 'invalid_token',
            ], 400);
        }
        $found = Event::where('title', '=', $request->get('title'));
        if ($found->first() != null)
            return response()->json([
                'message'=> 'Duplicated event',
            ], 400);

        $location = Location::where('id', '=', $request->get('location_id'))
                                ->where('owner_id', '=', $id)
                                ->first();
        if ($location == null)
            return response()->json([
                'message'=> 'Location not belongs to owner',
            ], 400);

        if ($request->get("type") != "public" && $request->get('type') != 'private' )
            return response()->json([
                'message'=> 'Invalid type of event',
            ], 400);

        $event = Event::create([
            'title' => $request->get('title'),
            'description' => $request->get('description'),
            'category' => $request->get('category'),
            'start_date' => strtotime($request->get('start_date')),
            'end_date' => strtotime($request->get('end_date')),
            'location_id' => $location->id,
            'owner_id' => $id,
            'type' => $request->get('type'),
            'capacity' => $request->get('capacity')
        ]);
        return response()->json([
            'message'=> 'Event created successfully',
            'data' => $event,
        ], 200);
    }

    public function updateEvent(Request $request, $id)
    {
        $token = JWTAuth::parseToken();
        $user_id = $token->getPayload()->get('sub');
        $user_type = $token->getPayload()->get('user_type');

        if (!$user_id || $user_type != 'Organizer') {
            return response()->json([
                'message' => 'invalid_token',
            ], 400);
        }

        $event = Event::where('id', '=', $id)->first();

        if ($event == null)
            return response()->json([
                'message'=> 'Event not found',
            ], 400);

        if ($request->get('location_id')) {
            $location = Location::where('id', '=', $request->get('location_id'))
                                ->where('owner_id', '=', $user_id)
                                ->first();
            if ($location == null) {
                return response()->json([
                    'message' => 'Location not belongs to owner',
                ], 400);
            }
        }

//        $existing_event = Event::where('location_id', '=', $request->get('location_id'));
//        if ($existing_event != null)
//            return response()->json([
//                'message'=> 'Location belongs to another event',
//            ], 400);

        if ($user_id != $event->owner_id)
            return response()->json([
                'message'=> 'Event Not belongs to owner',
            ], 400);

        $dict = $request->all();
        if (array_key_exists('start_date', $dict) !== false)
            $dict['start_date'] = strtotime($dict['start_date']);

        if (array_key_exists('end_date', $dict) !== false)
            $dict['end_date'] = strtotime($dict['end_date']);

        $event->update($dict);
        return response()->json([
            'message'=> 'Event updated successfully',
            'data'=>$event,
        ], 201);
    }

    public function deleteEvent(Request $request, $id)
    {

        $token = JWTAuth::parseToken();
        $user_id = $token->getPayload()->get('sub');
        $user_type = $token->getPayload()->get('user_type');

        if (!$user_id || $user_type != 'Organizer') {
            return response()->json([
                'message' => 'invalid_token',
            ], 400);
        }

        $event = Event::where('id', '=', $id)->first();

        if ($event == null)
            return response()->json([
                'message'=> 'Event not found',
            ], 400);

        if ($user_id != $event->owner_id)
            return response()->json([
                'message'=> 'Not belongs to owner',
            ], 400);

        $event->delete();
        return response()->json([
            'message'=> 'Event deleted successfully',
        ], 201);
    }

    public function listAll(Request $request)
    {
        $evs = Event::where('type', '=', 'public')
            ->paginate();
        $list_evs = array();

        foreach ($evs->items() as $ev) {
            $tmp = array('detail' => $ev);
            $owner = $ev->owner;
            $attendees = $ev->attendees;
            $location = $ev->location;

            $tmp['contact'] = $owner->email;
            $tmp['nummber_of_attendees'] = sizeof($attendees);
            $tmp['location_name'] = $location->name_location;
            $tmp['location_address'] = $location->address;
            array_push($list_evs, $tmp);
        }


        return response()->json([
            'current_page'=> $evs->currentPage(),
            'next_page_url'=> $evs->nextPageUrl(),
            'data'=> $list_evs
        ], 200);
    }

    public function getPublicEventsByAttendee(Request $request, $id)
    {
        $user = Attendee::where('id', '=', $id)->first();
        if ($user == null) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        $reserves = $user->events;
        $list_evs = array();
        foreach ($reserves as $re) {
            if ($re->type == 'public') {
                $tmp = array('data' => $re);
                $owner = $re->owner;
                $attendees = $re->attendees;
                $location = $re->location;

                $tmp['contact'] = $owner->email;
                $tmp['nummber_of_attendees'] = sizeof($attendees);
                $tmp['location_name'] = $location->name;
                $tmp['location_address'] = $location->address;
                array_push($list_evs, $tmp);
            }
        }

        return response()->json($list_evs, 200);
    }

    public function getPrivateEventsByAttendee(Request $request, $id)
    {
        $user = Attendee::where('id', '=', $id)->first();
        if ($user == null) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        $reserves = $user->events;
        $list_evs = array();
        foreach ($reserves as $re) {
            if ($re->type == 'private') {
                $tmp = array('data' => $re);
                $owner = $re->owner;
                $attendees = $re->attendees;
                $location = $re->location;

                $tmp['contact'] = $owner->email;
                $tmp['nummber_of_attendees'] = sizeof($attendees);
                $tmp['location_name'] = $location->name;
                $tmp['location_address'] = $location->address;
                array_push($list_evs, $tmp);
            }
        }

        return response()->json($list_evs, 200);
    }

    public function getInfo(Request $request, $id)
    {
        $found = Event::where('id', '=', $id)->first();
        if ($found == null)
            return response()->json([
                'message'=> 'Event not found',
            ], 400);


        $owner = $found->owner;
        $attendees = $found->attendees;
        $location = $found->location;

        if ($found->type == 'private') {
            $token = JWTAuth::parseToken();
            $user_id = $token->getPayload()->get('sub');
            $user_type = $token->getPayload()->get('user_type');

            if (!$user_id || $user_type != 'Attendee') {
                return response()->json([
                    'message' => 'invalid_token',
                ], 422);
            }

            $found_user = false;
            foreach ($attendees as $at) {
                if ($at->id == $user_id) {
                    $found_user = true;
                    break;
                }
            }

            if ($found_user == false)
                return response()->json([
                    'message'=> 'Permission denied',
                ], 400);
        }

        $result = array('detail' => $found,
                'contact' => $owner->email,
                'nummber_of_attendees' => sizeof($attendees),
                'location_name' => $location->name,
                'location_address' => $location->address);
        return response()->json(['result' => $result], 200);
    }

    public function getEventsByOwner(Request $request, $owner_id)
    {
        $owner = Organizer::where('id', '=', $owner_id)->first();
        if ($owner == null)
            return response()->json([
                'message'=> 'Owner not found',
            ], 400);

        $list_evs = Event::where('owner_id', '=', $owner_id)
            ->where('type', '=', 'public')
            ->paginate();

        return response()->json([
            'owner_id'=> $owner_id,
            'current_page'=> $list_evs->currentPage(),
            'next_page_url'=> $list_evs->nextPageUrl(),
            'data'=> $list_evs
        ], 200);
    }

    public function searchEvent(Request $request)
    {
        $event = Event::where('title', '=', $request->input('title'))->first();
        if ($event == null)
            return response()->json([
                'message'=> 'Event not found',
            ], 400);
        return response()->json(['result' => $event], 200);
    }

    public function getEventsByLocation(Request $request, $id)
    {
        $events = Event::where('location_id', '=', $id)
            ->where('type', '=', 'public')
            ->paginate();
        return response()->json(['result' => $events], 200);
    }

    public function getEventsStartBeforeDate(Request $request)
    {
        $my_date = strtotime($request->get('date'));
        $events = Event::where('start_date', '<=', $my_date)->paginate();
        return response()->json($events, 200);
    }

    public function getEventsAfterBeforeDate(Request $request)
    {
        $my_date = strtotime($request->get('date'));
        $events = Event::where('start_date', '>=', $my_date)->paginate();
        return response()->json($events, 200);
    }

    public function getEventsEndBeforeDate(Request $request)
    {
        $my_date = strtotime($request->get('date'));
        $events = Event::where('end_date', '<=', $my_date)->paginate();
        return response()->json($events, 200);
    }

    public function getEventsEndAfterDate(Request $request)
    {
        $my_date = strtotime($request->get('date'));
        $events = Event::where('end_date', '>=', $my_date)->paginate();
        return response()->json($events, 200);
    }

    public function getEventsByCategory(Request $request)
    {
        $category = $request->get('category');
        $events = Event::where('category', '=', $category)->paginate();
        return response()->json($events, 200);
    }

    function uploadImage(Request $rq, $id)
    {
        $token = JWTAuth::parseToken();
        $user_id = $token->getPayload()->get('sub');
        $user_type = $token->getPayload()->get('user_type');

        if (!$user_id || $user_type != 'Organizer') {
            return response()->json([
                'message' => 'invalid_token',
            ], 400);
        }

        $event = Event::where('id', '=', $id)->first();

        if ($event == null)
            return response()->json([
                'message'=> 'Event not found',
            ], 400);

        if ($user_id != $event->owner_id)
            return response()->json([
                'message'=> 'Not belongs to owner',
            ], 400);

        $rules = [ 'image' => 'image|max:10000' ];
        $posts = [ 'image' => $rq->file('image') ];

        $valid = Validator::make($posts, $rules);

        if ($valid->fails()) {
            return response()->json([
                'message'=> 'Validation failed',
            ], 400);
        }
        else {
            if ($rq->file('image')->isValid()) {
                $fileExtension = $rq->file('image')->getClientOriginalExtension();

                $fileName = time() . "_" . rand(0,9999999) . "_" . md5(rand(0,9999999)) . "." . $fileExtension;

                $uploadPath = public_path('/upload');

                $rq->file('image')->move($uploadPath, $fileName);
                $event->update([
                    'img' => $uploadPath . '/' . $fileName
                ]);
                return response()->json([
                    'message'=> 'Image uploaded',
                ], 200);
            }
            else {
                // Lỗi file
                return response()->json([
                    'message'=> 'Invalid image file',
                ], 400);
            }
        }
    }
}
