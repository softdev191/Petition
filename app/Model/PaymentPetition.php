<?php

namespace App\Model;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class PaymentPetition extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'payment_petition';
    
    public function getUser()
    {
        return $this->belongsTo('App\Model\User','sponsor_id','id');
    }
}
