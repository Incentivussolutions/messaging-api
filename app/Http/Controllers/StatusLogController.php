<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Helpers\Common;
use App\Models\StatusLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StatusLogController extends Controller
{
    /**
     * User Index
     * @param Request $request
     * @param Response $response
     */
    public function index(Request $request) {
        try {
            $response = [];
            // Validation given Data's
            $validator = array(
                'user'      => ['array', 'required'],
                'client'    => ['array', 'required'],
                'page'      => ['integer', 'required'],
                'limit'     => ['integer', 'required'],
                'sort'      => ['array', 'nullable'],
                'search'    => ['array', 'nullable'],
            );
            $validator = Common::validator($request, $validator);
            if ($validator && $validator->fails()) {
                return ApiResponse::send(203, null, $validator->errors()->first(), true);
            }
            $response = StatusLog::index($request);
            if ($response) {
                return ApiResponse::send(200, $response, 'Status Log list');
            }
            return ApiResponse::send(203);
        } catch(Exception $e) {
            Log::info($e);
            return ApiResponse::send(500);
        }
    }
}
