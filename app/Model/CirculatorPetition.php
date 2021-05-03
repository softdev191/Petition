<?php

namespace App\Model;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class CirculatorPetition extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'circulator_petition';
    
    public function getPetition()
    {
        return $this->belongsTo('App\Model\Petition', 'petition_id');
    }

     public function getUser()
    {
        return $this->belongsTo('App\Model\User','user_id','id');
    }
}
