<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Helpers\Common;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ClientController extends Controller
{
    /**
     * Create Client
     * @param Request $request
     * @param Response $response
     */
    public function create(Request $request) {
        try {
            $response = [];
            // Validation given Data's
            $validator = array(
                'id'            => ['integer', 'nullable'],
                'name'          => ['string', 'required'],
                'status'        => ['boolean', 'nullable'],
                'is_external'   => ['boolean', 'nullable'],
            );
            $validator = Common::validator($request, $validator);
            if ($validator && $validator->fails()) {
                return ApiResponse::send(203, null, $validator->errors()->first(), true);
            }
            $response = Client::store($request);
            if ($response) {
                return ApiResponse::send(201, $response, 'Client created successfully');
            }
            return ApiResponse::send(203);
        } catch(Exception $e) {
            Log::info($e);
            return ApiResponse::send(500);
        }
    }
}
