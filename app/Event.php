<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Event extends Model
{
    use Notifiable;
    protected $fillable = ['title', 'description', 'category', 'start_date',
        'end_date', 'location_id', 'owner_id', 'img', 'type', 'capacity'];

    protected $hidden = ['owner', 'attendees', 'pivot'];

    public function attendees()
    {
        return $this->belongsToMany('App\Attendee', 'reservations',
                                    'event_id', 'attendee_id');
    }

    public function owner()
    {
        return $this->belongsTo('App\Organizer', 'owner_id', 'id');
    }

    public function location()
    {
        return $this->belongsTo('App\Location', 'location_id', 'id');
    }
}
