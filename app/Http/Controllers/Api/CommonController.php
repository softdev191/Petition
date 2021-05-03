<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use Illuminate\Support\Facades\Validator;
use App\Model\State;
use App\Model\Notification;
use App\Model\Contact;
use App\Model\SignerList;

use Hash;
use Response;
use Config;
use JWTAuth;
use App\Helper\ImageHelper;

use Illuminate\Support\Facades\Auth;


class CommonController extends Controller
{   

    public function stateList(){

        $stateList=State::get();
        return $this->sendResponse(Config::get('constants.status.OK'),$stateList, null);
    }

    
    public function upload(Request $request){
        
        set_time_limit(0);
        //text-40000.txt
        $file = '/var/www/html/public/'.$request->name;
        $customerArr = $this->csvToArray($file);
        foreach ($customerArr as $key => $value) {
            # code...
            $data = new SignerList();
            $data->voter_id = isset($customerArr[$key][0]) ? $customerArr[$key][0] : '';
            $data->country_code = isset($customerArr[$key][1]) ? $customerArr[$key][1] : '';
            $data->country = isset($customerArr[$key][2]) ? $customerArr[$key][2] : '';
            $data->last_name = isset($customerArr[$key][3]) ? $customerArr[$key][3] : '';
            $data->first_name = isset($customerArr[$key][4]) ? $customerArr[$key][4] : '';
            $data->middle_name = isset($customerArr[$key][5]) ? $customerArr[$key][5] : '';
            $data->name_suffix = isset($customerArr[$key][6]) ? $customerArr[$key][6] : '';
            $data->voter_name = isset($customerArr[$key][7]) ? $customerArr[$key][7] : '';
            $data->status_code = isset($customerArr[$key][8]) ? $customerArr[$key][8] : '';
            $data->precinct_name = isset($customerArr[$key][9]) ? $customerArr[$key][9] : '';
            $data->address_library_id = isset($customerArr[$key][10]) ? $customerArr[$key][10] : '';
            $data->house_num = isset($customerArr[$key][11]) ? $customerArr[$key][11] : '';
            $data->house_suffix = isset($customerArr[$key][12]) ? $customerArr[$key][12] : '';
            $data->pre_dir = isset($customerArr[$key][13]) ? $customerArr[$key][13] : '';
            $data->street_name = isset($customerArr[$key][14]) ? $customerArr[$key][14] : '';
            $data->street_type = isset($customerArr[$key][15]) ? $customerArr[$key][15] : '';
            $data->post_dir = isset($customerArr[$key][16]) ? $customerArr[$key][16] : '';
            $data->unit_type = isset($customerArr[$key][17]) ? $customerArr[$key][17] : '';
            $data->unit_num = isset($customerArr[$key][18]) ? $customerArr[$key][18] : '';
            $data->residential_address = isset($customerArr[$key][19]) ? $customerArr[$key][19] : '';
            $data->residential_city = isset($customerArr[$key][20]) ? $customerArr[$key][20] : '';
            $data->residential_state = isset($customerArr[$key][21]) ? $customerArr[$key][21] : '';
            $data->residential_zip_code = isset($customerArr[$key][22]) ? $customerArr[$key][22] : '';
            $data->residential_zip_plus = isset($customerArr[$key][23]) ? $customerArr[$key][23] : '';
            $data->effective_date = isset($customerArr[$key][24]) ? $customerArr[$key][24] : '';
            $data->registration_date = isset($customerArr[$key][25]) ? $customerArr[$key][25] : '';
            $data->status = isset($customerArr[$key][26]) ? $customerArr[$key][26] : '';
            $data->status_reason = isset($customerArr[$key][27]) ? $customerArr[$key][27] : '';
            $data->birth_year = isset($customerArr[$key][28]) ? $customerArr[$key][28] : '';
            $data->gender = isset($customerArr[$key][29]) ? $customerArr[$key][29] : '';
            $data->precinct = isset($customerArr[$key][30]) ? $customerArr[$key][30] : '';
            $data->split = isset($customerArr[$key][31]) ? $customerArr[$key][31] : '';
            $data->voter_status_id = isset($customerArr[$key][32]) ? $customerArr[$key][32] : '';
            $data->party = isset($customerArr[$key][33]) ? $customerArr[$key][33] : '';
            $data->preference = isset($customerArr[$key][34]) ? $customerArr[$key][34] : '';
            $data->party_affiliation_date = isset($customerArr[$key][35]) ? $customerArr[$key][35] : '';
            $data->phone_num = isset($customerArr[$key][36]) ? $customerArr[$key][36] : '';
            $data->mail_addr1 = isset($customerArr[$key][37]) ? $customerArr[$key][37] : '';
            $data->mail_addr2 = isset($customerArr[$key][38]) ? $customerArr[$key][38] : '';
            $data->mail_addr3 = isset($customerArr[$key][39]) ? $customerArr[$key][39] : '';
            $data->mailing_city = isset($customerArr[$key][40]) ? $customerArr[$key][40] : '';
            $data->mailing_zip_code = isset($customerArr[$key][41]) ? $customerArr[$key][41] : '';
            $data->mailing_zip_plus = isset($customerArr[$key][42]) ? $customerArr[$key][42] : '';
            $data->mailing_country = isset($customerArr[$key][43]) ? $customerArr[$key][43] : '';
            $data->spl_id = isset($customerArr[$key][44]) ? $customerArr[$key][44] : '';
            $data->permanent_mail_in_voter = isset($customerArr[$key][45]) ? $customerArr[$key][45] : '';
            $data->congressional = isset($customerArr[$key][46]) ? $customerArr[$key][46] : '';
            $data->state_senate = isset($customerArr[$key][47]) ? $customerArr[$key][47] : '';
            $data->state_house = isset($customerArr[$key][48]) ? $customerArr[$key][48] : '';
            $data->id_required = isset($customerArr[$key][49]) ? $customerArr[$key][49] : '';
            $data->save();
        }
        

        return $this->sendResponse(Config::get('constants.status.OK'),count($customerArr), null);
    }

    public function csvToArray($filename = '', $delimiter = ',')
    {
        if (!file_exists($filename) || !is_readable($filename))
            return false;

        $header = null;
        $data = array();
        if (($handle = fopen($filename, 'r')) !== false)
        {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false)
            {
                $data[] = $row;
            }
            fclose($handle);
        }

        return $data;
    }

    
    public function getFeedBack(){
        
        $feedbackList=Contact::with('getUser')
        ->orderBy('created_at', 'desc')
        ->limit(20)
        ->get();
        foreach ($feedbackList as $key => $value) {
            if(isset($value->getUser)){
                ImageHelper::getSingleImageUrl($value->getUser,'profile_picture');
            }
        }
        
        return $this->sendResponse(Config::get('constants.status.OK'),$feedbackList, null);
    }

    public function contact(Request $request){

        $validator = Validator::make($request->all(),
        [
            'user_id' => 'required',
            'description' => 'required'
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
            $data= new Contact();
            $data->user_id = $request->user_id;
            $data->description = $request->description;
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
