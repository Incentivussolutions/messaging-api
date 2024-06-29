<?php

namespace App\Http\Middleware;

use Closure;
use App\Helpers\Token;
use App\Models\Client;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;

class AuthenticateCommon
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
        if ($request->getPathInfo() == '/api/client/create') {
            return $next($request);
        }
        if (!@$request->client) {
            return ApiResponse::send(401, null, "Invalid url");
        }
        $client = Client::where('unique_id', '=', $request->client)
                        ->where('status', '=', 1)
                        ->first();
        if (!$client) {
            return ApiResponse::send(401, null, "Invalid request");
        }
        $request->merge(['client' => $client->toArray()]);

        return $next($request);
    }
}
