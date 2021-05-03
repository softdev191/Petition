<?php

namespace App\Http\Middleware;

use Closure;

use App\Exceptions\UnAuthorizedRequestException;
use App\Libraries\APIResponse;
use Illuminate\Http\Response;
use Config;


class ValidateClient
{   
    use APIResponse;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    
    
    public function handle($request, Closure $next)
    {
        $client_id = $request->header('clientauth');
       
        if($client_id==Config::get('constants.clientauth')){
            return $next($request);
        }
        return $this->sendResponse(Config::get('error.code.NOT_FOUND'),
                null,
                ['Unauthorized Request'],
                Config::get('error.code.NOT_FOUND'));
    }
}
