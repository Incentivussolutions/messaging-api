<?php

namespace App\Http\Controllers;

use App\Models\WhatsappConfig;
use App\Helpers\Common;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;

class WhatsappConfigController extends Controller
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
            $response = WhatsappConfig::index($request);
            if ($response) {
                return ApiResponse::send(200, $response, 'Configurations list');
            }
            return ApiResponse::send(203);
        } catch(Exception $e) {
            Log::info($e);
            return ApiResponse::send(500);
        }
    }

    /**
     * User Form Data
     * @param Request $request
     * @param Response $response
     */
    public function formData(Request $request) {
        try {
            $response = [];
            // Validation given Data's
            $validator = array(
                'user'      => ['array', 'required'],
                'client'    => ['array', 'required'],
                'id'        => ['integer', 'nullable'],
            );
            $validator = Common::validator($request, $validator);
            if ($validator && $validator->fails()) {
                return ApiResponse::send(203, null, $validator->errors()->first(), true);
            }
            $response['data'] = WhatsappConfig::first($request);
            if ($response) {
                return ApiResponse::send(200, $response, 'Configuration form data');
            }
            return ApiResponse::send(203);
        } catch(Exception $e) {
            Log::info($e);
            return ApiResponse::send(500);
        }
    }

    /**
     * User Get List
     * @param Request $request
     * @param Response $response
     */
    public function getList(Request $request) {
        try {
            $response = [];
            // Validation given Data's
            $validator = array(
                'user'      => ['array', 'required'],
                'client'    => ['array', 'required'],
            );
            $validator = Common::validator($request, $validator);
            if ($validator && $validator->fails()) {
                return ApiResponse::send(203, null, $validator->errors()->first(), true);
            }
            $response = WhatsappConfig::get($request);
            if ($response) {
                return ApiResponse::send(200, $response, 'Configurations list');
            }
            return ApiResponse::send(203);
        } catch(Exception $e) {
            Log::info($e);
            return ApiResponse::send(500);
        }
    }


    /**
     * User Store
     * @param Request $request
     * @param Response $response
     */
    public function store(Request $request) {
        try {
            $response = [];
            // Validation given Data's
            $validator = array(
                'user'          => ['array', 'required'],
                'client'        => ['array', 'required'],
                'id'            => ['integer', 'nullable'],
                'account_name'  => ['string', 'nullable'],
                'provider'      => ['integer', 'nullable'],
                'sender_id'     => ['string', 'nullable'],
                'application_id'=> ['string', 'nullable'],
                'api_key'       => ['string', 'nullable'],
                'api_secret'    => ['string', 'nullable'],
                'status'        => ['integer', 'nullable']
            );
            if (!@$request->id) {
                $validator['file'] = ['file', 'required'];
            }
            $validator = Common::validator($request, $validator);
            if ($validator && $validator->fails()) {
                return ApiResponse::send(203, null, $validator->errors()->first(), true);
            }
            $response = WhatsappConfig::store($request);
            if ($response) {
                return ApiResponse::send(201, $response, 'Configuration stored successfully');
            }
            return ApiResponse::send(203);
        } catch(Exception $e) {
            Log::info($e);
            return ApiResponse::send(500);
        }
    }
}
