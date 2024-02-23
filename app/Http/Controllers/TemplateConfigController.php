<?php

namespace App\Http\Controllers;

use App\Helpers\Common;
use App\Models\CommonData;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\TemplateConfig;
use App\Models\WhatsappConfig;
use Illuminate\Support\Facades\Log;

class TemplateConfigController extends Controller
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
            $response = TemplateConfig::index($request);
            if ($response) {
                return ApiResponse::send(200, $response, 'Template Configurations list');
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
            $response['data'] = TemplateConfig::first($request);
            if (@$response['data']) {
                $response['data']['header_url'] = TemplateConfig::getHeaderUrl($request, $response['data']);
            }
            $response['whatsapp_configs'] = WhatsappConfig::get($request);
            $response['languages'] = CommonData::get('TEMPLATE_LANGUAGE');
            $response['input_types'] = CommonData::get('INPUT_TYPE');
            $response['field_types'] = CommonData::get('FIELD_TYPE');
            $response['placeholders'] = CommonData::get('PLACHOLDER');
            if ($response) {
                return ApiResponse::send(200, $response, 'Template Configuration form data');
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
            $response = TemplateConfig::get($request);
            if ($response) {
                return ApiResponse::send(200, $response, 'Template Configurations list');
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
                'template_name' => ['string', 'required'],
                'template_type' => ['integer', 'required'],
                'whatspp_config_id'=> ['integer', 'required'],
                'template_content' => ['string', 'required'],
                'status'        => ['integer', 'nullable']
            );
            if (@$request->template_type && $request->template_type == 2) {
                $validator['custom_content_type']   = ['integer', 'required'];
                if (@$request->custom_content_type && $request->custom_content_type != 1) {
                    $validator['custom_field_type']     = ['integer', 'required'];
                    if (@$request->custom_field_type && $request->custom_field_type != 1) {
                        $validator['template_content'] = ['string', 'nullable'];
                    }
                }
            } else {
                $validator['template_key']  = ['string', 'required'];
                $validator['language_id']   = ['string', 'required'];
                $validator['header']        = ['array', 'required'];
                $validator['footer']        = ['array', 'required'];
            }
            $validator = Common::validator($request, $validator);
            if ($validator && $validator->fails()) {
                return ApiResponse::send(203, null, $validator->errors()->first(), true);
            }
            $response = TemplateConfig::store($request);
            if ($response) {
                return ApiResponse::send(201, $response, 'Template Configuration stored successfully');
            }
            return ApiResponse::send(203);
        } catch(Exception $e) {
            Log::info($e);
            return ApiResponse::send(500);
        }
    }
}
