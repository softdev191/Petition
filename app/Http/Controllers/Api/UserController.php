<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use Illuminate\Support\Facades\Validator;
use App\Model\User;
use Hash;
use Response;
use Config;
use JWTAuth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Helper\ImageHelper;

class UserController extends Controller
{   

    public function getAuthUser(Request $request){

        $user = JWTAuth::parseToken()->authenticate();
        return $this->sendResponse(Config::get('constants.status.OK'),$user, null); 
    }

    public function login(Request $request){
         $validator = Validator::make($request->all(),
         [
             'email' => 'required|email',
             'password' => 'required'
         ]);

        if($validator->fails()){

            return $this->sendResponse (
                Config::get ( 'error.code.BAD_REQUEST' ),
                null,
                $validator->getMessageBag ()->all (),
                Config::get ( 'error.code.BAD_REQUEST' )

            );
        }
        try{
            $user=User::where('email',$request->email)->first();
            
            if ($user&&Hash::check($request->password, $user->password)) { 
                if($user->isdelete==1){
                    return $this->sendResponse( Config::get('error.code.NOT_FOUND'),
                    null,
                    ['User blocked.Please contact admin']
                    ); 
                }else{
                    $userToken=JWTAuth::fromUser($user);
                    $user->token = $userToken;
                    $user = ImageHelper::getSingleImageUrl($user,'profile_picture');

                    return $this->sendResponse(Config::get('constants.status.OK'),$user, null); 
                }
                
            }else{
                return $this->sendResponse( Config::get('error.code.NOT_FOUND'),
                    null,
                    [Config::get('error.message.USER_NOT_FOUND')],
                    Config::get('error.code.NOT_FOUND')
                );
            }
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

    public function allUser(){

        $userList=User::where('isdelete',0)->get();
        $user = ImageHelper::getImageUrl($userList,'profile_picture');
        return $this->sendResponse(Config::get('constants.status.OK'),$user, null);
    }

    public function circulatoruser(){

        $userList=User::where('user_type','circulator')->get();
        return $this->sendResponse(Config::get('constants.status.OK'),$userList, null);
    }

    public function removeUser($user_id){

        $data=User::find($user_id);
        $data->isdelete = 1;
        $data->save();
        return $this->sendResponse(Config::get('constants.status.OK'),$data, null);
    }
    
    
    public function checkSigner(Request $request){

       $validator = Validator::make($request->all(),
        [
            'name' => 'required',
            'location' => 'required'
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
            $data= User::where('first_name',$request->name)
                        ->where('address',$request->location)
                        ->where('user_type','signer')
                        ->get();
           
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

    public function signUp(Request $request){

        $validator = Validator::make($request->all(),
            [
                'first_name' => 'required|max:255',
                'last_name' => 'max:255',
                'password' => 'required|min:6',
                'email' => 'required|email|unique:users',
                'user_type'=> 'required',
                'state_id'=> 'required'
            ]);

        if($validator->fails()){

            return $this->sendResponse (
                Config::get ( 'error.code.BAD_REQUEST' ),
                null,
                $validator->getMessageBag ()->all (),
                Config::get ( 'error.code.BAD_REQUEST' )

            );
        }
        try{

            $user = new User();
            $user->first_name   = $request->first_name;
            $user->last_name   = isset($request->last_name) ? $request->last_name : '';
            $user->password   = bcrypt($request->password);
            $user->email      = $request->email;
            $user->user_type  = $request->user_type;
            $user->state_id   = $request->state_id;
            $user->address    = isset($request->address) ? $request->address : '';
            $user->city       = isset($request->city) ? $request->city : '';
            $user->country    = isset($request->country) ? $request->country : '';
            $user->birthday   = isset($request->birthday) ? $request->birthday : null;
            $user->gender      = isset($request->gender) ? $request->gender : '';
            $user->language_id      = isset($request->language_id) ? $request->language_id : 1;
            $user->petition_trained  = isset($request->petition_trained) ? $request->petition_trained : 0;
            $user->volunteer_paid    = isset($request->volunteer_paid) ? $request->volunteer_paid : 0;
            $user->lat  = isset($request->lat) ? $request->lat : '';
            $user->lon = isset($request->lon) ? $request->lon : '';
            $user->legally_petition  = isset($request->legally_petition) ? $request->legally_petition : 0;
            $user->save();

            return $this->sendResponse(Config::get('constants.status.OK'),null, null);
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

    public function updteProfile(Request $request,$id){

       
        try{

            $user = User::find($id);
            if($user){
                $user->first_name   = isset($request->first_name) ? $request->first_name : $user->first_name;
                $user->last_name   = isset($request->last_name) ? $request->last_name : $user->last_name;
                $user->state_id   = isset($request->state_id) ? $request->state_id : $user->state_id;
                $user->address    = isset($request->address) ? $request->address :  $user->address;
                $user->city       = isset($request->city) ? $request->city :  $user->city;
                $user->country    = isset($request->country) ? $request->country :  $user->country;
                $user->birthday   = isset($request->birthday) ? $request->birthday : $user->birthday;
                $user->gender      = isset($request->gender) ? $request->gender : $user->gender;
                $user->phone_number      = isset($request->phone_number) ? $request->phone_number : $user->phone_number;
                $user->language_id      = isset($request->language_id) ? $request->language_id : $user->language_id;
                $user->petition_trained  = isset($request->petition_trained) ? $request->petition_trained : $user->petition_trained;
                $user->volunteer_paid    = isset($request->volunteer_paid) ? $request->volunteer_paid : $user->volunteer_paid;
                $user->lat  = isset($request->lat) ? $request->lat : $user->lat;
                $user->lon = isset($request->lon) ? $request->lon : $user->lon;
                $user->legally_petition  = isset($request->legally_petition) ? $request->legally_petition :  $user->legally_petition;
                
                $imagePath = Config::get('constants.profile_path');
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
                    $image_name = (string) Str::uuid()."_".'profile_picture'.'.png';
                    $file->move($imagePath, $image_name);
                    array_push($imageArray,$imagePath.$image_name);
                }
                if((count($imageArray) > 0)){
                    ImageHelper::deleteImage($user->profile_picture);
                    $user->profile_picture = $imageArray[0];
                }
                
                $user->save();
                $user = ImageHelper::getSingleImageUrl($user,'profile_picture');

                 return $this->sendResponse(Config::get('constants.status.OK'),$user, null);
            }else{
                 return $this->sendResponse(Config::get ( 'error.code.BAD_REQUEST' ),null, 'profile not update');
            }
           

           
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
