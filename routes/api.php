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

Route::group(['prefix' => 'attendee'], function () {
    Route::post('register', 'AttendeeController@register');
    Route::post('login', 'AttendeeController@login');

    Route::group(['middleware' => \App\Http\Middleware\VerifyJWTToken::class, ],
        function () {
            Route::get('info', 'AttendeeController@getCurrentInfo');
            Route::put('info/update', 'AttendeeController@updateInfo');
        });
});

Route::group(['prefix' => 'organizer'], function () {
    Route::post('register', 'OrganizerController@register');
    Route::post('login', 'OrganizerController@login');
    Route::get('list', 'OrganizerController@listAll');
    Route::get('info/{id}', 'OrganizerController@getSpecificInfo');

    Route::group(['middleware' => \App\Http\Middleware\VerifyJWTToken::class ],
        function () {
            Route::get('info', 'OrganizerController@getCurrentInfo');
            Route::put('info/update', 'OrganizerController@updateInfo');
        });
});

Route::group(['prefix' => 'location'], function () {
    Route::get('list', 'LocationController@listAll');
    Route::get('info/{id}', 'LocationController@getInfo');
    Route::get('list/{id}', 'LocationController@getLocationsByOwner');

    Route::group(['middleware' => \App\Http\Middleware\VerifyJWTToken::class ],
        function () {
            Route::post('new', 'LocationController@createLocation');
            Route::put('update/{id}', 'LocationController@updateLocation');
            Route::delete('delete/{id}', 'LocationController@deleteLocation');
        });
});

Route::group(['prefix' => 'event'], function () {
    Route::get('list', 'EventController@listAll');
    Route::get('info/{id}', 'EventController@getInfo');
    Route::get('list/{id}', 'EventController@getEventsByOwner');
    Route::get('search', 'EventController@searchEvent');

    Route::group(['middleware' => \App\Http\Middleware\VerifyJWTToken::class ],
        function () {
            Route::post('new', 'EventController@createEvent');
            Route::put('update/{id}', 'EventController@updateEvent');
            Route::delete('delete/{id}', 'EventController@deleteEvent');
            Route::post('upload/{id}', 'EventController@uploadImage');
            Route::get('attendee', 'AttendeeController@getPrivateEventsByAttendee');
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

Route::group(['prefix' => 'reservation'], function () {
    Route::group(['middleware' => \App\Http\Middleware\VerifyJWTToken::class ],
        function () {
            Route::post('public', 'ReservationController@reservePublicEvent');
            Route::post('private', 'ReservationController@reservePrivateEvent');
            Route::post('confirm', 'ReservationController@confirmEvent');
        });
});