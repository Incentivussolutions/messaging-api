<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Client;
use App\Helpers\Common;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateClient
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if(!$request->get('auth_key')) {
            return ApiResponse::send(401);
        }

        // Check where header "Authorization" set
        if ($request->get('auth_key')) {
            $auth_key = $request->auth_key;

            try {
                $client = Client::getClientByAuthKey($auth_key);
                if (!@$client) {
                    return ApiResponse::send(203, null, "Auth Key is not valid");
                }
                if (!Common::changeClient(@$client->unique_id)) {
                    return ApiResponse::send(203, null, "Client is not valid");
                }
                if (@$client->is_external) {
                    // Additional authentication required
                }
                $request_merge = [
                    'client' => $client->toArray()
                ];
                $request->merge($request_merge);
            } catch (Exception $e) {
                return ApiResponse::send(401);
            }
        } else {
            return ApiResponse::send(401);
        }
        return $next($request);
    }
}
