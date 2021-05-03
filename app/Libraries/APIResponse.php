<?php

namespace App\Libraries;
use Response;
use Log;

trait APIResponse 
{

	public function sendResponse($status_code = 200, $response = null, $error = [], $custom_error_code = null)
    {	
    	$status = ($status_code === 200) ? true : false;
    	$response= !empty($response) ? $response: $response;
    	$error = !empty($error) ? [
    			'custom_code' => $custom_error_code,
    			'message' => $error
    	]: null;
    	
    	$return = array(
			'status' 	=> $status,
    		'response' 	=> $response,
    		'error' 	=> $error
		);
    	
    	//Log::info(print_r($return,true));
		//$data = json_encode($return);
		return response()->json($return,$status_code);
    	//return Response::json($data, $status_code);
    }

    
    
}