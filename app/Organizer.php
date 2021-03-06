<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Auth\Authenticatable;

class Organizer extends Model implements JWTSubject, AuthenticatableContract
{
    use Notifiable;
    use Authenticatable;

    protected $fillable = ['firstname', 'lastname', 'email', 'phone', 'password'];
    protected $hidden = [
        'password', 'owner'
    ];
//    protected $guard = 'api_organizer';
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        // TODO: Implement getJWTIdentifier() method.
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        // TODO: Implement getJWTCustomClaims() method.
        return [];
    }

    public function events()
    {
        return $this->hasMany('App\Event', 'owner_id', 'id');
    }
}
