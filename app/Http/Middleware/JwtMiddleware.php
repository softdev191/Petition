<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Exception;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use App\Libraries\APIResponse;
use Config;

class JwtMiddleware 
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
        try {
            //$user = JWTAuth::user();
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            $tokenMsg ='';
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                $tokenMsg = 'Token is Invalid';
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                $tokenMsg = 'Token is Expired';
            }else{
                $tokenMsg = 'Authorization Token not found';
                //$tokenMsg =  $request->header('Authorization1');
            }
            return $this->sendResponse(Config::get('error.code.UNAUTHORIZED_REQUEST'),
                null,
                [$tokenMsg],
                Config::get('error.code.UNAUTHORIZED_REQUEST'));
        }
        return $next($request);
    }
}