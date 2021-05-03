<?php

namespace App\Model;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class VoterList extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var arraya
     */
    protected $table = 'voter_list';

    public function getPetition()
    {
        return $this->belongsTo('App\Model\Petition', 'petition_id');
    }

    public function getCirculatorPetition()
    {
        return $this->belongsTo('App\Model\CirculatorPetition', 'petition_id','petition_id');
    }

    public function getSignedPetition()
    {
        return $this->belongsTo('App\Model\Signed', 'petition_id','petition_id');
    }

    public function getUser()
    {
        return $this->belongsTo('App\Model\User','user_id','id');
    }
    public function getCirculatorUser()
    {
        return $this->belongsTo('App\Model\User','circulator_id');
    }
    public function getSignerUser()
    {
        return $this->belongsTo('App\Model\User','signer_id');
    }
    
    
    
}
