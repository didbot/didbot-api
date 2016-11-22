<?php

namespace Didbot\DidbotApi\Test\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Get the dids for the given user.
     */
    public function dids()
    {
        return $this->hasMany('Didbot\DidbotApi\Models\Did');
    }

    /**
     * Get the tags for the given user.
     */
    public function tags()
    {
        return $this->hasMany('Didbot\DidbotApi\Models\Tag');
    }
}
