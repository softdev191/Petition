<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use Illuminate\Support\Facades\Validator;
use App\Model\State;
use Hash;
use Response;
use Config;
use JWTAuth;

use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Api\NotificationController;
use App\Model\CirculatorPetition;
use App\Model\VoterList;
use App\Model\Donate;
use App\Model\FeedBackCirculator;
use App\Helper\ImageHelper;


class CirculatorController extends Controller
{   

    
    public function circulatorSignerList($circulator_id){

        try{
            $circularPetition=VoterList::with('getSignerUser')
            ->where('circulator_id',$circulator_id)
            ->where('verify',0)
            ->where('reject',0)
            ->get();
            $tempArray = array();
            foreach ($circularPetition as $key => $value) {
                if(isset($value->getSignerUser)){
                    ImageHelper::getSingleImageUrl($value->getSignerUser,'profile_picture');
                    array_push($tempArray,$value);
                }
            }
            return $this->sendResponse(Config::get('constants.status.OK'),$tempArray, null);
        }catch(\Illuminate\Database\QueryException $e){

            return $this->sendResponse(
                Config::get('error.code.INTERNAL_SERVER_ERROR'),
                null,
                [$e->errorInfo[2]],
                $e->errorInfo[0]
            );
        }

    }

    public function getVoter($voter_id){

        try{
            $data=VoterList::where('id',$voter_id)->first();
            return $this->sendResponse(Config::get('constants.status.OK'),$data, null);
              
        }catch(\Illuminate\Database\QueryException $e){

            return $this->sendResponse(
                Config::get('error.code.INTERNAL_SERVER_ERROR'),
                null,
                [$e->errorInfo[2]],
                $e->errorInfo[0]
            );
        }

    }

    
    public function acceptSigner($voterid){

       
        try{
            $data=VoterList::find($voterid);
            $data->accept = 1;
            $data->status ='accept';
            $data->save();
            NotificationController::sendNotification($data->circulator_id,$data->signer_id,'Meeting Accepted','');
            return $this->sendResponse(Config::get('constants.status.OK'),$data, null);
        }catch(\Illuminate\Database\QueryException $e){

            return $this->sendResponse(
                Config::get('error.code.INTERNAL_SERVER_ERROR'),
                null,
                [$e->errorInfo[2]],
                $e->errorInfo[0]
            );
        }
    }


    public function rejectSigner($voterid){

        try{
            $data=VoterList::find($voterid);
            $data->reject = 1;
            $data->status ='reject';
            $data->save();
            NotificationController::sendNotification($data->circulator_id,$data->signer_id,'Meeting Rejected','');
            return $this->sendResponse(Config::get('constants.status.OK'),$data, null);
        }catch(\Illuminate\Database\QueryException $e){

            return $this->sendResponse(
                Config::get('error.code.INTERNAL_SERVER_ERROR'),
                null,
                [$e->errorInfo[2]],
                $e->errorInfo[0]
            );
        }
       
    }

    
    public function signerCirculatorList($petitionid){

        try{
            $circularPetition=CirculatorPetition::with('getPetition')
            ->with('getUser')
            ->where('petition_id',$petitionid)
            ->get();
            return $this->sendResponse(Config::get('constants.status.OK'),$circularPetition, null);
        }catch(\Illuminate\Database\QueryException $e){

            return $this->sendResponse(
                Config::get('error.code.INTERNAL_SERVER_ERROR'),
                null,
                [$e->errorInfo[2]],
                $e->errorInfo[0]
            );
        }

        
    }

    public function createMeeting(Request $request){

        $validator = Validator::make($request->all(),
        [
            'petition_id' => 'required',
            'circulator_id' => 'required',
            'signer_id'=> 'required'
        ]);
        if($validator->fails()){

            return $this->sendResponse (
                Config::get ( 'error.code.BAD_REQUEST'),
                null,
                $validator->getMessageBag ()->all(),
                Config::get('error.code.BAD_REQUEST')
            );
        }
        try{
            $data=VoterList::where('user_id',$request->signer_id)
                            ->where('petition_id',$request->petition_id)
                            ->first();
            if($data){
                $data->circulator_id=$request->circulator_id;
                $data->save();
            }
        
            return $this->sendResponse(Config::get('constants.status.OK'),$data, null);

        }
        catch(\Illuminate\Database\QueryException $e){

            return $this->sendResponse(
                Config::get('error.code.INTERNAL_SERVER_ERROR'),
                null,
                [$e->errorInfo[2]],
                $e->errorInfo[0]
            );
        }
        
    }

    public function verifyCirculator(Request $request){
         $validator = Validator::make($request->all(),
        [
            'voter_id' => 'required',
            'verify' => 'required',
        ]);
        if($validator->fails()){

            return $this->sendResponse (
                Config::get ( 'error.code.BAD_REQUEST'),
                null,
                $validator->getMessageBag ()->all(),
                Config::get('error.code.BAD_REQUEST')
            );
        }
        try{
            $data=VoterList::find($request->voter_id);
            $data->verify=$request->verify;
            $data->status ='verify';
            $data->save();
            NotificationController::sendNotification($data->circulator_id,$data->signer_id,'Sucessfully Verified','');
            return $this->sendResponse(Config::get('constants.status.OK'),$data, null);

        }
        catch(\Illuminate\Database\QueryException $e){

            return $this->sendResponse(
                Config::get('error.code.INTERNAL_SERVER_ERROR'),
                null,
                [$e->errorInfo[2]],
                $e->errorInfo[0]
            );
        }
    }

    public function addFeedBack(Request $request){

        //id, signer_id, petition_id, circulator_id, comment, rating, created_at, updated_at
        $validator = Validator::make($request->all(),
       [
           'signer_id' => 'required',
           'petition_id' => 'required',
           'circulator_id' => 'required',
           'comment' => 'required',
           'rating' => 'required'
       ]);
       if($validator->fails()){

           return $this->sendResponse (
               Config::get ( 'error.code.BAD_REQUEST'),
               null,
               $validator->getMessageBag ()->all(),
               Config::get('error.code.BAD_REQUEST')
           );
       }
       try{
           $data=new FeedBackCirculator();
           $data->signer_id=$request->signer_id;
           $data->petition_id=$request->petition_id;
           $data->circulator_id=$request->circulator_id;
           $data->comment=$request->comment;
           $data->rating=$request->rating;
           $data->save();
           return $this->sendResponse(Config::get('constants.status.OK'),$data, null);

       }
       catch(\Illuminate\Database\QueryException $e){

           return $this->sendResponse(
               Config::get('error.code.INTERNAL_SERVER_ERROR'),
               null,
               [$e->errorInfo[2]],
               $e->errorInfo[0]
           );
       }
   }


}
