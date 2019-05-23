<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Location extends Model
{
    use Notifiable;
    protected $fillable = ['name_location', 'address', 'owner_id'];

    protected $hidden = ['owner', 'events'];

    public function owner()
    {
        return $this->belongsTo('App\Organizer', 'id', 'owner_id');
    }

    public function events()
    {
        return $this->hasMany('App\Event');
    }
}
