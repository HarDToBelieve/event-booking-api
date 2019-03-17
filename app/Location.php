<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Location extends Model
{
    use Notifiable;
    protected $fillable = ['name_location', 'address', 'capacity', 'owner_id'];
}
