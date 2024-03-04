<?php

namespace App\Http\Controllers;

use App\Helpers\Common;
use App\Helpers\Vonage;
use App\Models\ResponseLog;
use App\Models\TargetQueue;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\TemplateConfig;
use App\Models\WhatsappConfig;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
    public function sendMessage(Request $request) {
        try {
            $response = [];
            // Validation given Data's
            $validator = array(
                'client'        => ['array', 'required'],
                'template_id'   => ['string', 'required'],
                'to_send'       => ['array', 'required'],
            );
            if (@$request->get('to_Send')) {
                foreach($request->get('to_send') as $key => $value) {
                    $validator['to_send'.$key.'to_number']  = ['required', 'string'];
                    $validator['to_send'.$key.'parameters'] = ['required', 'array'];
                }
            }
            $validator = Common::validator($request, $validator);
            if ($validator && $validator->fails()) {
                return ApiResponse::send(203, null, $validator->errors()->first(), true);
            }
            $template = TemplateConfig::getTemplateByTemplateID($request->template_id);
            if (!@$template) {
                return ApiResponse::send(203, null, "Template Not Found");
            }
            $config = WhatsappConfig::getWhatsappConfigByID(@$template->whatspp_config_id);
            if (!@$config && !@$wa->sender_id) {
                return ApiResponse::send(203, null, "No Whatsapp Configurations Found");
            }
            $target_queue = TargetQueue::store($request);
            $response = Vonage::sendMessage($request, $config, $template, $target_queue);
            if ($response) {                
                return ApiResponse::send(200, null, "Message sent successfully");
            } else {
                return ApiResponse::send(203);
            }
        } catch(Exception $e) {
            Log::info($e);
            return ApiResponse::send(500);
        }
    }

    public function queueMessage(Request $request) {
        try {
            $response = [];
            // Validation given Data's
            $validator = array(
                'client'        => ['array', 'required'],
                'template_id'   => ['string', 'required'],
                'to_send'       => ['array', 'required'],
            );
            if (@$request->get('to_Send')) {
                foreach($request->get('to_send') as $key => $value) {
                    $validator['to_send'.$key.'to_number']  = ['required', 'string'];
                    $validator['to_send'.$key.'parameters'] = ['required', 'array'];
                }
            }
            $validator = Common::validator($request, $validator);
            if ($validator && $validator->fails()) {
                return ApiResponse::send(203, null, $validator->errors()->first(), true);
            }
            $template = TemplateConfig::getTemplateByTemplateID($request->template_id);
            if (!@$template) {
                return ApiResponse::send(203, null, "Template Not Found");
            }
            $config = WhatsappConfig::getWhatsappConfigByID(@$template->whatspp_config_id);
            if (!@$config && !@$wa->sender_id) {
                return ApiResponse::send(203, null, "No Whatsapp Configurations Found");
            }
            $target_queue = TargetQueue::store($request);
            Common::changeClient();
            Message::dispatch($template, $config, $request->all());
            $response = Vonage::sendMessage($request, $config, $template);
            if ($response) {
                $res_store = array(
                    'target_queue' => $target_queue,
                    'response'     => $response
                );
                Log::info($res_store);
                ResponseLog::store((Object) $res_store);
                return ApiResponse::send(200, null, "Message sent successfully");
            } else {
                return ApiResponse::send(203);
            }
            return ApiResponse::send(200, null, "Messages added in the queue");
        } catch(Exception $e) {
            Log::info($e);
            return ApiResponse::send(500);
        }
    }

    public function downloadWhatsappTemplates(Request $request) {
        try {
            $response = [];
            // Validation given Data's
            // $validator = array(
            //     'user'      => ['array', 'required'],
            //     'client'    => ['array', 'required'],
            //     'page'      => ['integer', 'required'],
            //     'limit'     => ['integer', 'required'],
            //     'sort'      => ['array', 'nullable'],
            //     'search'    => ['array', 'nullable'],
            // );
            // $validator = Common::validator($request, $validator);
            // if ($validator && $validator->fails()) {
            //     return ApiResponse::send(203, null, $validator->errors()->first(), true);
            // }
            $response = TemplateConfig::downloadWhatsappTemplates($request);
            dd($response);
        } catch(Exception $e) {
            Log::info($e);
            return ApiResponse::send(500);
        }
    }
}
