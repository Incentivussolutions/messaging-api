<?php

namespace App\Http\Controllers;

use App\Jobs\Status;
use App\Jobs\Message;
use App\Helpers\Common;
use App\Helpers\Vonage;
use App\Models\StatusLog;
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
            $key_file = $config->key_file;
            $target_queue = TargetQueue::store($request);
            $header_url = TemplateConfig::getHeaderUrl($request->client['id'], $template);
            $template = $template->toArray();
            $config = $config->toArray();
            $config['key_file'] = $key_file;
            $template['header_url'] = $header_url;
            $target_queue = $target_queue->toArray();
            Common::changeClient();
            foreach($request->to_send as $key => $value) {
                Message::dispatch($request->client, $value['to_number'], $value['parameters'], $template, $config, $target_queue)->onQueue('high');
            }
            $response = array(
                'batch_id' => $target_queue['id']
            );
            return ApiResponse::send(200, $response, "Messages added in the queue successfully");
        } catch(Exception $e) {
            Log::info($e);
            return ApiResponse::send(500);
        }
    }

    public function getStatus(Request $request) {
        try {
            $response = [];
            // Validation given Data's
            $validator = array(
                'client'        => ['array', 'required'],
                'batch_id'      => ['integer', 'required'],
            );
            $validator = Common::validator($request, $validator);
            if ($validator && $validator->fails()) {
                return ApiResponse::send(203, null, $validator->errors()->first(), true);
            }
            $response = StatusLog::getStatus($request);
            return ApiResponse::send(200, $response, "Status List");
        } catch(Exception $e) {
            Log::info($e);
            return ApiResponse::send(500);
        }
    }

    public function getTemplates(Request $request) {
        try {
            $response = [];
            // Validation given Data's
            $validator = array(
                'client'        => ['array', 'required']
            );
            $validator = Common::validator($request, $validator);
            if ($validator && $validator->fails()) {
                return ApiResponse::send(203, null, $validator->errors()->first(), true);
            }
            $response = TemplateConfig::getClientTemplates($request);
            if ($response) {
                foreach($response as $key => $value) {
                    $url = TemplateConfig::getPreviewUrl($request->client['id'], $value);
                    $response[$key]['preview_url'] = (@$url[0]) ? $url[0] : null;
                }
            } else {
                return ApiResponse::send(203, null, "Error getting templates");
            }
            return ApiResponse::send(200, $response, "Template List");
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

    public function status(Request $request) {
        try {
            Log::info($request->all());
            if (@$request->client_ref) {
                Status::dispatch($request->all());
            }
            return ApiResponse::send(200);
        } catch (Exception $e) {
            Log::info($e);
            return ApiResponse::send(500);
        }
    }
}
