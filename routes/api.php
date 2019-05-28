<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::group(['middleware' => \App\Http\Middleware\Cors::class, ],
    function () {
        Route::group(['prefix' => 'attendees'], function () {
            Route::post('register', 'AttendeeController@register');
            Route::post('login', 'AttendeeController@login');

            Route::group(['middleware' => \App\Http\Middleware\VerifyJWTToken::class,],
                function () {
                    Route::get('{id}/events', 'EventController@getPublicEventsByAttendee');
                    Route::get('private_events', 'EventController@getPrivateEventsByAttendee');
                    Route::get('profile', 'AttendeeController@getCurrentInfo');
                    Route::put('profile/update', 'AttendeeController@updateInfo');
                });
        });

        Route::group(['prefix' => 'organizers'], function () {
            Route::post('register', 'OrganizerController@register');
            Route::post('login', 'OrganizerController@login');
            Route::get('', 'OrganizerController@listAll');
            Route::get('{id}/events', 'EventController@getEventsByOwner');

            Route::group(['middleware' => \App\Http\Middleware\VerifyJWTToken::class],
                function () {
                    Route::get('profile', 'OrganizerController@getCurrentInfo');
                    Route::put('profile', 'OrganizerController@updateInfo');
                    Route::get('{id}/locations', 'LocationController@getLocationsByOwner');
                });

            Route::get('{id}', 'OrganizerController@getSpecificInfo');
        });

        Route::group(['prefix' => 'locations'], function () {
            Route::get('', 'LocationController@listAll');
//            Route::get('{id}', 'EventController@getEventsByLocation');

            Route::group(['middleware' => \App\Http\Middleware\VerifyJWTToken::class],
                function () {
                    Route::post('', 'LocationController@createLocation');
                    Route::put('{id}', 'LocationController@updateLocation');
                    Route::delete('{id}', 'LocationController@deleteLocation');
                });

            Route::get('{id}', 'LocationController@getInfo');
        });

        Route::group(['prefix' => 'events'], function () {
            Route::get('', 'EventController@listAll');

            Route::group(['middleware' => \App\Http\Middleware\VerifyJWTToken::class],
                function () {
                    Route::post('', 'EventController@createEvent');
                    Route::get('organizer_events', 'EventController@getEventsByOrganizer');
                    Route::put('{id}', 'EventController@updateEvent');
                    Route::delete('{id}', 'EventController@deleteEvent');
                    Route::post('{id}/upload', 'EventController@uploadImage');
                    Route::get('{id}/reservations', 'EventController@getAttendeesByEvent');
                    Route::get('{id}', 'EventController@getInfo');
                });
        });

        //Route::group(['prefix' => 'voucher'], function () {
        //
        //
        //    Route::group(['middleware' => \App\Http\Middleware\VerifyJWTToken::class ],
        //        function () {
        //            Route::post('new', 'VoucherController@createVoucher');
        //        });
        //});

        Route::group(['prefix' => 'reservations'], function () {
            Route::group(['middleware' => \App\Http\Middleware\VerifyJWTToken::class],
                function () {
                    Route::post('', 'ReservationController@handleEvent');
                    Route::post('{id}/confirm', 'ReservationController@confirmEvent');
                });
        });
    });