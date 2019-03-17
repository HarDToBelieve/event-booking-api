<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Reservation extends Model
{
    use Notifiable;
    protected $fillable = ['status', 'event_id', 'attendee_id', 'voucher_id'];
}
