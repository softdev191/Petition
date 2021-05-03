<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use Illuminate\Support\Facades\Validator;

use App\Model\PaymentSigner;
use App\Model\PaymentPetition;
use App\Model\PaymentGateway;
use App\Model\Donate;
use App\Model\VoterList;

use App\Model\Notification;
use App\Http\Controllers\Api\NotificationController;
use App\Helper\ImageHelper;


use Hash;
use Response;
use Config;
use JWTAuth;

use Illuminate\Support\Facades\Auth;


class PaymentController extends Controller
{   

    public function addSignerPayment(Request $request){

        //id, circulator_id, signer_id, petition_id, transaction_id, payment_gateway_id, start_price, distance, price, bonus, created_at, updated_at
        $validator = Validator::make($request->all(),
        [
            'petition_id' => 'required',
            'circulator_id' => 'required',
            'signer_id'=> 'required',
            'price'=> 'required',
            'distance'=> 'required',
            'transaction_id'=> 'required',
            'start_price'=> 'required',
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
            
            $data=new PaymentSigner();
            $data->price =$request->price;
            $data->bonus = isset($request->bonus) ? $request->bonus : 0;
            $data->signer_id =$request->signer_id;
            $data->circulator_id =$request->circulator_id;
            $data->petition_id =$request->petition_id;
            $data->transaction_id =$request->transaction_id;
            $data->transaction_info = $request->transaction_info;
            $data->distance =$request->distance;
            $data->start_price =$request->start_price;
            $data->save();

            if(isset($request->id)){

            }else{
                $voterdata = VoterList::find($request->voter_id);
                $voterdata->signer_id = $request->signer_id;
                $voterdata->circulator_id = $request->circulator_id;
                $voterdata->status = 'waiting';
                $voterdata->save(); 
                NotificationController::sendNotification($request->signer_id,$request->circulator_id,'Meeting Invited',''); 

            }
            

            return $this->sendResponse(Config::get('constants.status.OK'),$data, null);

        } catch(\Illuminate\Database\QueryException $e){

            return $this->sendResponse(
                Config::get('error.code.INTERNAL_SERVER_ERROR'),
                null,
                [$e->errorInfo[2]],
                $e->errorInfo[0]
            );
        }
           
    }

    public function updateSignerPayment(Request $request,$id){

        //id, circulator_id, signer_id, petition_id, transaction_id, payment_gateway_id, start_price, distance, price, bonus, created_at, updated_at
        $validator = Validator::make($request->all(),
        [
            'petition_id' => 'required',
            'circulator_id' => 'required',
            'signer_id'=> 'required',
            'price'=> 'required',
            'distance'=> 'required',
            'transaction_id'=> 'required',
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

            $data=PaymentSigner::find($id);
            $data->price =$request->price;
            $data->bonus = isset($request->bonus) ? $request->bonus : $data->bonus;
            $data->signer_id =$request->signer_id;
            $data->circulator_id =$request->circulator_id;
            $data->petition_id =$request->petition_id;
            $data->transaction_id =$request->transaction_id;
            $data->transaction_info = $request->transaction_info;
            $data->distance =$request->distance;
            $data->start_price =$request->start_price;
            $data->save();

            

            return $this->sendResponse(Config::get('constants.status.OK'),$data, null);

        } catch(\Illuminate\Database\QueryException $e){

            return $this->sendResponse(
                Config::get('error.code.INTERNAL_SERVER_ERROR'),
                null,
                [$e->errorInfo[2]],
                $e->errorInfo[0]
            );
        }
           
    }

    public function getSignerPayment(Request $request){

        //id, circulator_id, signer_id, petition_id, transaction_id, payment_gateway_id, start_price, distance, price, bonus, created_at, updated_at
        $validator = Validator::make($request->all(),
        [
            'petition_id' => 'required',
            'circulator_id' => 'required',
            'signer_id'=> 'required',
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

            $data=PaymentSigner::where('signer_id',$request->signer_id)
                                ->where('petition_id',$request->petition_id)
                                ->where('circulator_id',$request->circulator_id)
                                ->first();

           

            return $this->sendResponse(Config::get('constants.status.OK'),$data, null);

        } catch(\Illuminate\Database\QueryException $e){

            return $this->sendResponse(
                Config::get('error.code.INTERNAL_SERVER_ERROR'),
                null,
                [$e->errorInfo[2]],
                $e->errorInfo[0]
            );
        }
           
    }

    public function donatePayment(Request $request){

        //id, price, payment_gateway_id, description, user_id, transaction_id, created_at, updated_at
        $validator = Validator::make($request->all(),
        [
            
            'user_id'=> 'required',
            'price'=> 'required',
            'transaction_id'=> 'required',
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

            $data=new Donate();
            $data->price = $request->price;
            $data->user_id = $request->user_id;
            $data->description = isset($request->description) ? $request->description : '';
            $data->transaction_id = $request->transaction_id;
            $data->transaction_info = $request->transaction_info;
            $data->save();

            return $this->sendResponse(Config::get('constants.status.OK'),$data, null);

        } catch(\Illuminate\Database\QueryException $e){

            return $this->sendResponse(
                Config::get('error.code.INTERNAL_SERVER_ERROR'),
                null,
                [$e->errorInfo[2]],
                $e->errorInfo[0]
            );
        }
           
    }

    public function paymentGatewayList($userid){

        $data=PaymentGateway::where('user_id',$userid)->get();
        return $this->sendResponse(Config::get('constants.status.OK'),$data, null);
    }

    public function donateHistory(){

        $data=Donate::with('getUser')->get();
        $tempArray = array();
        foreach ($data as $key => $value) {   
            if(isset($value->getUser)){
                ImageHelper::getSingleImageUrl($value->getUser,'profile_picture');
                array_push($tempArray,$value);
            }
        }
        return $this->sendResponse(Config::get('constants.status.OK'),$tempArray, null);
    }

    public function paymentHistory(){

        $paymentSigner=PaymentSigner::with('getUser')->get();
        $paymentPetition=PaymentPetition::with('getUser')->get();
        
        $tempArray = array();
        foreach ($paymentSigner as $key => $value) {   
            $value->paymenttype='signer';
            if(isset($value->getUser)){
                ImageHelper::getSingleImageUrl($value->getUser,'profile_picture');
                array_push($tempArray,$value);
            }
        }
        foreach ($paymentPetition as $key => $value) {   
            $value->paymenttype='sponsor';
            if(isset($value->getUser)){
                ImageHelper::getSingleImageUrl($value->getUser,'profile_picture');
                array_push($tempArray,$value);
            }
        }
        return $this->sendResponse(Config::get('constants.status.OK'),$tempArray, null);
    }
    
    public function paypalToken(){

        $ch = curl_init();
        $clientId = "AXnSphtMfiuUTqGtC1Bg3ptTodvlobQbiLU8TmKJdVoTcfHunxkDIZnYoi0M51_ZSJ4h41IRbxwzlXHa";
        $secret = "EMs0N7OppsMIAm3gvSeURFqtQ3Z0uZ_zwfH64vjg6JjMarb-ejf91753-5Vy5VedPmOzFz8L4VOX8j8-";

        curl_setopt($ch, CURLOPT_URL, "https://api.sandbox.paypal.com/v1/oauth2/token");
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($ch, CURLOPT_USERPWD, $clientId.":".$secret);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");

        $result = curl_exec($ch);

        if(empty($result))die("Error: No response.");
        else
        {
            $json = json_decode($result);
            return $this->sendResponse(Config::get('constants.status.OK'),$json, null);
        }

        curl_close($ch);
    }
}
