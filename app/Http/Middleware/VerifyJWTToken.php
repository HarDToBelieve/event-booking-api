<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class VerifyJWTToken
{
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
            JWTAuth::parseToken();
            $user = JWTAuth::toUser($request->header('token'));
        }catch (JWTException $e) {
            if($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                return response()->json(['token_expired'], 401);
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                return response()->json(['token_invalid'], 401);
            }else{
                return response()->json(['error'=>'Token is required']);
            }
        }
        return $next($request);
    }
}
