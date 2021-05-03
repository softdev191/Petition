<?php

namespace App\Model;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Petition extends Authenticatable
{
    use Notifiable;
    /**
     * The attributes that are mass assignable.
     *
     * @var arraya
     */
    protected $table = 'petition';
    
    public function getVoterList()
    {
        return $this->hasMany('App\Model\VoterList');
    }

    public function matchSigner() {
        return $this->getVoterList()->where('name','=', 'signed');
    }

    public function getCirculatorPetition()
    {   
       return $this->hasMany('App\Model\CirculatorPetition');
       //return $this->hasManyThrough('App\Model\CirculatorPetition', 'App\Model\User','id','user_id');
       // return $this->belongsToMany('App\Model\CirculatorPetition','App\Model\User');
    }

    public function getUser()
    {
        return $this->belongsTo('App\Model\User','user_id','id');
    }
}
