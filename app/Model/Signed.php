<?php

namespace App\Model;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Signed extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var arraya
     */
    protected $table = 'signed';
    
    public function getUser()
    {
        return $this->belongsTo('App\Model\User','user_id','id');
    }
}
