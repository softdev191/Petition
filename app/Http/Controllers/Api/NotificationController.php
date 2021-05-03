<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use Illuminate\Support\Facades\Validator;
use App\Model\State;
use App\Model\Notification;
use App\Model\Contact;

use Hash;
use Response;
use Config;
use JWTAuth;

use Illuminate\Support\Facades\Auth;


class NotificationController extends Controller
{   

    public function notificationList($id){
        
        $notificationList=Notification::with('getUser')
                        ->where('from_id',$id)
                        ->orderBy('created_at', 'desc')
                        ->limit(10)
                        ->get();
        return $this->sendResponse(Config::get('constants.status.OK'),$notificationList, null);
    }

    public static function sendNotification($toid,$fromid,$title,$message){

        //id, from_id, to_id, title, message, created_at, updated_at
        $notification=new Notification();
        $notification->from_id = $fromid;
        $notification->to_id = $toid;
        $notification->title = $title;
        $notification->message = $message;
        $notification->save();

    }
}
