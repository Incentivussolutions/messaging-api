<?php

namespace App\Http\Middleware;

use Closure;
use App\Helpers\Token;
use App\Helpers\Common;
use Illuminate\Support\Arr;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\AppAccessToken;

class AuthenticateApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if(!$request->header('Authorization')) {
            return ApiResponse::send(401);
        }

        // Check where header "Authorization" set
        if ($request->header('Authorization')) {
            $token   = explode(' ', $request->header('Authorization'));
            $token   = isset($token[1]) ? $token[1] : '';

            if($token) {
                try {
                    $decoded = Token::decode($token);
                    // Validate auth_token then only allow
                    if ($decoded && isset($decoded['auth_token']) && !Token::validate($decoded)) {
                        return ApiResponse::send(203, null, "Auth token is not valid");
                    }
                    if (isset($decoded['is_refresh']) && $decoded['is_refresh'] == true) {
                        return ApiResponse::send(401, null, "Invalid access token");
                    }
                    if (!isset($decoded['user'])) {
                        return ApiResponse::send(203, null, "User is not valid");
                    }
                    if (!isset($decoded['client'])) {
                        return ApiResponse::send(203, null, "Client is not valid");
                    }
                    if (!isset($decoded['access_id'])) {
                        return ApiResponse::send(401);
                    }
                    if ($decoded && @$decoded['expires_at'] && Token::isExpired($decoded)) {
                        return ApiResponse::send(401, null, "Token Expired. Please login again.");
                    }
                    if (!Common::changeClient(@$decoded['client']->unique_id)) {
                        return ApiResponse::send(203, null, "Client is not valid");
                    }
                    $active_token = AppAccessToken::where('access_token', '=', $token)->where('is_active', '=', 1)->first();
                    if (!$active_token) {
                        return ApiResponse::send(401);
                    }
                    if (@$request->refresh_token) {
                        $decoded_refresh_token = Token::decode($request->refresh_token);
                        if (Token::isExpired($decoded_refresh_token)) {
                            return ApiResponse::send(401);
                        }
                    }
                    $request_merge = [
                        'user' => (array)$decoded['user'], 
                        'client' => (array)$decoded['client'], 
                        'access_id' => $decoded['access_id']
                    ];
                    if (@$request->form) {
                        $request_merge['form'] = $request->form;
                    }
                    $request->merge($request_merge);
                } catch (Exception $e) {
                    return ApiResponse::send(401, null, 'Your token invalid');
                }
            } else {
                return ApiResponse::send(401);
            }
        } else {
            return ApiResponse::send(401);
        }
        return $next($request);
    }
}
