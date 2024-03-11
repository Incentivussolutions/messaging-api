<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Helpers\Common;
use App\Models\CommonData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CommonDataController extends Controller
{
    /**
     * Common Data Index
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
            $response = CommonData::index($request);
            if ($response) {
                return ApiResponse::send(200, $response, 'Common Data list');
            }
            return ApiResponse::send(203);
        } catch(Exception $e) {
            Log::info($e);
            return ApiResponse::send(500);
        }
    }

    /**
     * common Data Form Data
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
            $response['data'] = CommonData::first($request);
            if ($response) {
                return ApiResponse::send(200, $response, 'Common Data form data');
            }
            return ApiResponse::send(203);
        } catch(Exception $e) {
            Log::info($e);
            return ApiResponse::send(500);
        }
    }

    /**
     * CommonData Get List
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
            $response = CommonData::get(@$request->type);
            if ($response) {
                return ApiResponse::send(200, $response, 'Common Data list');
            }
            return ApiResponse::send(203);
        } catch(Exception $e) {
            Log::info($e);
            return ApiResponse::send(500);
        }
    }


    /**
     * Common Data Store
     * @param Request $request
     * @param Response $response
     */
    public function store(Request $request) {
        try {
            $response = [];
            // Validation given Data's
            $validator = array(
                'user'      => ['array', 'required'],
                'client'    => ['array', 'required'],
                'id'        => ['integer', 'nullable'],
                'type'      => ['string', 'required'],
                'value'     => ['string', 'required'],
                'description'  => ['string', 'required']
            );
            $validator = Common::validator($request, $validator);
            if ($validator && $validator->fails()) {
                return ApiResponse::send(203, null, $validator->errors()->first(), true);
            }
            $response = CommonData::store($request);
            if ($response) {
                return ApiResponse::send(201, $response, 'Common Data stored successfully');
            }
            return ApiResponse::send(203);
        } catch(Exception $e) {
            Log::info($e);
            return ApiResponse::send(500);
        }
    }

    /**
     * Common Data Delete
     * @param Request $request
     * @param Response $response
     */
    public function delete(Request $request) {
        try {
            $response = [];
            // Validation given Data's
            $validator = array(
                'user'      => ['array', 'required'],
                'client'    => ['array', 'required'],
                'id'        => ['integer', 'required'],
            );
            $validator = Common::validator($request, $validator);
            if ($validator && $validator->fails()) {
                return ApiResponse::send(203, null, $validator->errors()->first(), true);
            }
            $response = CommonData::destroy($request);
            if ($response) {
                return ApiResponse::send(201, $response, 'Common Data deleted successfully');
            }
            return ApiResponse::send(203);
        } catch(Exception $e) {
            Log::info($e);
            return ApiResponse::send(500);
        }
    }
}
