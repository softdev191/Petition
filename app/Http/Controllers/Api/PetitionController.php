<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

use App\Model\Petition;
use App\Model\CirculatorPetition;
use App\Model\VoterList;
use App\Model\PaymentPetition;
use App\Model\Signed;
use App\Model\User;


use DB;
use Hash;
use Response;
use Config;
use JWTAuth;
use App\Helper\ImageHelper;

use Illuminate\Support\Facades\Auth;


class PetitionController extends Controller
{   
    
    public function sponsorPetition($sponsorid){

        $listPetition=Petition::with('getVoterList')
                                ->with('getCirculatorPetition')
                                ->where('user_id',$sponsorid)
                                ->orderBy('updated_at', 'desc')
                                ->get();
        
       
        $listPetition = ImageHelper::getImageUrl($listPetition,'image_url');
        return $this->sendResponse(Config::get('constants.status.OK'),$listPetition, null);
    }

    public function allPetition(){

        $listPetition=Petition::with('getVoterList')
                                ->with('getCirculatorPetition')
                                ->orderBy('updated_at', 'desc')
                                ->get();
        
       
        $listPetition = ImageHelper::getImageUrl($listPetition,'image_url');
        return $this->sendResponse(Config::get('constants.status.OK'),$listPetition, null);
    }

    

    public function signerPetition($stateid,$name){

        $signerPetition=VoterList::with('getCirculatorUser')
        ->with(['getPetition' => function ($query) use($stateid){
            $query->where('state_id','=', $stateid);
        }])->where('name',$name)->orderBy('updated_at', 'desc')->get();

        $tempArray = array();
        foreach ($signerPetition as $key => $value) {
            if(isset($value->getPetition)){
                $value->get_petition=ImageHelper::getSingleImageUrl($value->getPetition,'image_url');
                if(isset($value->getCirculatorUser)){
                    $value->get_circulator_user=ImageHelper::getSingleImageUrl($value->getCirculatorUser,'profile_picture');
                }
                array_push($tempArray,$value);
            }
        }
        
        return $this->sendResponse(Config::get('constants.status.OK'),$tempArray, null);
    }

    public function circulatorPetition($circulatorid){

        $circularPetition=CirculatorPetition::with('getPetition')
                                ->where('user_id',$circulatorid)
                                ->orderBy('updated_at', 'desc')
                                ->get();
        return $this->sendResponse(Config::get('constants.status.OK'),$circularPetition, null);
    }
    

    public function petitionDetails($id){

        $listPetition=Petition::with('getVoterList')
                                ->with('getCirculatorPetition')
                                ->where('id',$id)
                                ->first();
        $userId = array();   
        foreach ($listPetition->getCirculatorPetition as $key1 => $value1) {
            
            if(array_search($value1->user_id, $userId)==false){
                array_push($userId,$value1->user_id);
            }
        } 
        $users =  User::whereIn('id', $userId)->get();
        $users = ImageHelper::getImageUrl($users,'profile_picture');
        $listPetition->users = $users;
        $listPetition = ImageHelper::getSingleImageUrl($listPetition,'image_url');
        return $this->sendResponse(Config::get('constants.status.OK'),$listPetition, null);
    }

     public function reviewHistory($userid){
        
        $listPetition=Signed::with('getUser')->where('user_id',$userid)
                                ->get();
        return $this->sendResponse(Config::get('constants.status.OK'),$listPetition, null);
    }

    

    public function signedPetition(Request $request){
        $validator = Validator::make($request->all(),
        [
            'petition_id' => 'required',
            'user_id' => 'required',
            'rating' => 'required',
            'comment' => 'required',
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
            $data=new Signed();
            $data->petition_id=$request->petition_id;
            $data->user_id=$request->user_id;
            $data->rating=$request->rating;
            $data->comment=$request->comment;
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

    public function addPetition(Request $request){

        //id, subject, description, terms, video_url, sponsor_id, state_id, created_at, updated_at
        $regex = '/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/';
        $validator = Validator::make($request->all(),
        [
            'subject' => 'required|max:255',
            'description' => 'required|max:555',
            'state_id' => 'required',
            'user_id' => 'required',
            'terms' => 'required|max:255',
            'image_url'=>'required',
            "video_url" => "required"
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

            $imagePath = Config::get('constants.petition_path');
            $imageArray = array();
            $file = $request->file('image_url');
            if($file){
                $rules = array('file' => 'max:2048');
                $fileArray = array('file' => $file);
                $validator = Validator::make($fileArray, $rules);
                if ($validator->fails()) {
                        $messages = $validator->messages();
                        return $this->sendResponse (
                            Config::get ( 'error.code.BAD_REQUEST'),
                            null,
                            [$validator->messages()],
                            Config::get ( 'error.code.BAD_REQUEST')
                        );
                }
                $image_name = (string) Str::uuid()."_".'petition'.'.png';
                $file->move($imagePath, $image_name);
                array_push($imageArray,$imagePath.$image_name);
            }

            $data = new Petition();
            $data->subject = isset($request->subject) ? $request->subject : '';
            $data->description = isset($request->description) ? $request->description : '';
            $data->terms = isset($request->terms) ? $request->terms : '';
            $data->video_url = isset($request->video_url) ? $request->video_url : '';
            $data->user_id  = $request->user_id;
            $data->state_id = isset($request->state_id) ? $request->state_id :1;
            if((count($imageArray)>0)){
                $data->image_url = $imageArray[0];
            }
            $data->save();

            if(isset($request->circulator_id)){

                $list = ($request->circulator_id);
                for ($i=0; $i<count($list) ; $i++) {
                    // code...
                    $data1=new CirculatorPetition();
                    $data1->petition_id =$data->id;
                    $data1->user_id = $list[$i];
                    $data1->save();
                }
            }
            
            if(isset($request->signer_id)){
                $list = ($request->signer_id);
                for ($i=0; $i<count($list); $i++) {
                    // code...
                    $jsonData = json_decode($list[$i]);

                    $data2=new VoterList();
                    $data2->petition_id =$data->id;
                    $data2->name = $jsonData->first_name;
                    $data2->location = $jsonData->location;
                    $data2->save();
                }
            }

            $data3 = new PaymentPetition();
            $data3->petition_id =$data->id;
            $data3->sponsor_id =$request->user_id;
            $data3->type = $request->price_type;
            $data3->price = $request->price;
            $data3->transaction_info = $request->transaction_info;
            $data3->transaction_id = $request->transaction_id;
            $data3->save();

            $listPetition=Petition::with('getVoterList')
                                ->with('getCirculatorPetition')
                                ->where('id',$data->id)
                                ->first();

            return $this->sendResponse(Config::get('constants.status.OK'), $listPetition, null);  
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

    public function updatePetition(Request $request, $id){

        //id, subject, description, terms, video_url, sponsor_id, state_id, created_at, updated_at
        $regex = '/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/';
        $validator = Validator::make($request->all(),
        [
            'subject' => 'required|max:255',
            'description' => 'required|max:555',
            'state_id' => 'required',
            'user_id' => 'required',
            'terms' => 'required|max:255',
            "video_url" => "required"
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

            $imagePath = Config::get('constants.petition_path');
            $imageArray = array();
            $file = $request->file('image_url');
            if($file){
                $rules = array('file' => 'max:2048');
                $fileArray = array('file' => $file);
                $validator = Validator::make($fileArray, $rules);
                if ($validator->fails()) {
                        $messages = $validator->messages();
                        return $this->sendResponse (
                            Config::get ( 'error.code.BAD_REQUEST'),
                            null,
                            [$validator->messages()],
                            Config::get ( 'error.code.BAD_REQUEST')
                        );
                }
                $image_name = (string) Str::uuid()."_".'petition'.'.png';
                $file->move($imagePath, $image_name);
                array_push($imageArray,$imagePath.$image_name);
            }

            $data = Petition::find($id);
            $data->subject = isset($request->subject) ? $request->subject : $data->subject;
            $data->description = isset($request->description) ? $request->description : $request->description;
            $data->terms = isset($request->terms) ? $request->terms : $request->terms;
            $data->video_url = isset($request->video_url) ? $request->video_url : $request->video_url ;
            $data->user_id  = isset($request->user_id) ? $request->user_id : $request->user_id ;
            $data->state_id = isset($request->state_id) ? $request->state_id :$request->state_id;
            if((count($imageArray)>0)){
                ImageHelper::deleteImage($data->image_url);
                $data->image_url = $imageArray[0];
            }
            
            $data->save();

            if(isset($request->circulator_id)){
                CirculatorPetition::where('petition_id', $id)->delete();
                $list = ($request->circulator_id);
                for ($i=0; $i<count($list) ; $i++) {
                    // code...
                    $data1=new CirculatorPetition();
                    $data1->petition_id =$id;
                    $data1->user_id = $list[$i];
                    $data1->save();
                }
            }
            
            if(isset($request->signer_id)){
                $list = ($request->signer_id);
                VoterList::where('petition_id', $id)->delete();
                for ($i=0; $i<count($list); $i++) {
                    // code...
                    $jsonData = json_decode($list[$i]);
                    $data2=new VoterList();
                    $data2->petition_id =$id;
                    $data2->name = $jsonData->first_name;
                    $data2->location = $jsonData->location;
                    $data2->save();
                }
            }

            // $data3 = new PaymentPetition();
            // $data3->petition_id =$data->id;
            // $data3->type = $request->price_type;
            // $data3->price = $request->price;
            // $data3->transaction_info = $request->transaction_info;
            // $data3->transaction_id = $request->transaction_id;
            // $data3->save();

            $listPetition=Petition::with('getVoterList')
                                ->with('getCirculatorPetition')
                                ->where('id',$data->id)
                                ->first();

            return $this->sendResponse(Config::get('constants.status.OK'), $listPetition, null);  
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
