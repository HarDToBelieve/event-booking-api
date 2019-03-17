<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Voucher extends Model
{
    use Notifiable;
    protected $fillable = ['event_id', 'discount_percent', 'start_date', 'end_date', 'code'];
}
