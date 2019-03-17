<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Event extends Model
{
    use Notifiable;
    protected $fillable = ['title', 'description', 'category', 'start_date',
        'end_date', 'location_id', 'owner_id', 'img', 'type'];
}
