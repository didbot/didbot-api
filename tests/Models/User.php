<?php

namespace Didbot\DidbotApi\Test\Models;

use Didbot\DidbotApi\Traits\Uuids;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use Didbot\DidbotApi\Traits\HasDids;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens, HasDids, Uuids;

    public $incrementing = false;

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
}
