<?php

namespace App\Helpers;

use DB;
use Exception;
use Vonage\Client;
use App\Models\CommonData;
use App\Models\ResponseLog;
use Illuminate\Support\Str;
use App\Models\TemplateConfig;
use Illuminate\Support\Facades\Log;
use Vonage\Application\Application;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Vonage\Client\Credentials\Keypair;
use Vonage\Messages\Channel\WhatsApp\WhatsAppText;
use Vonage\Messages\Channel\WhatsApp\WhatsAppCustom;

class Vonage {
    public static $channel = 'whatsapp';
    public static $webhook_url = 'https://example.com/status';
    public static $webhook_version = 'v1';
    public static $policy = 'deterministic';
    public static $default_locale = 'en_US';
    public static $message_types = array(
        'text',
        'image',
        'audio',
        'video',
        'file',
        'template',
        'sticker',
        'custom'
    );
    public static function sendMessage($request, $config, $template, $target_queue = null, $from_queue = false) {
        try {
            $client = @$request->client;
            $from_number = $config->sender_id;
            self::setClientConfig($config);
            $application_id = config('vonage.application_id');
            $vonage_client = new Client(new Keypair(config('vonage.private_key'), new Application($application_id)));
            if ($from_queue) {
                $to_number  = $request->to_number;
                $parameters = $request->parameters;
                if ($template->template_type == 1) {
                    $components = self::getComponents($client, $template, $parameters);
                    $custom = array(
                        'type' => "template",
                        'template' => array(
                            'namespace' => $template->template_key,
                            'name' => $template->template_namespace,
                            'language' => array(
                                'policy' => self::$policy,
                                'code'   => @$template->language->code
                            ),
                            'components' => $components
                        )
                    );
                    $base_message = new WhatsAppCustom($to_number, $from_number, $custom);
                } else {
                    // Others
                    $message = "";
                    $base_message = new WhatsAppText($to_number, $from_number, $message);
                }
                if ($base_message) {
                    $base_message->setClientRef(@$client['unique_id']);
                    Log::info("Message");
                    Log::info($base_message->getCustom());
                    $response = $vonage_client->messages()->send($base_message);
                }
            } else {
                foreach($request->to_send as $key => $value) {
                    $to_number  = $value['to_number'];
                    $parameters = $value['parameters'];
                    if ($template->template_type == 1) {
                        $components = self::getComponents($client, $template, $parameters);
                        $custom = array(
                            'type' => "template",
                            'template' => array(
                                'namespace' => $template->template_key,
                                'name' => $template->template_namespace,
                                'language' => array(
                                    'policy' => self::$policy,
                                    'code'   => @$template->language->code
                                ),
                                'components' => $components
                            )
                        );
                        $base_message = new WhatsAppCustom($to_number, $from_number, $custom);
                    } else {
                        // Others
                        $message = "";
                        $base_message = new WhatsAppText($to_number, $from_number, $message);
                    }
                    if ($base_message) {
                        $base_message->setClientRef(@$client['unique_id']);
                        Log::info($base_message->getCustom());
                        $response = $vonage_client->messages()->send($base_message);
                        $res_store = array(
                            'target_queue'  => $target_queue,
                            'to_number'     => $to_number,
                            'response'      => $response
                        );
                        Log::info($res_store);
                        ResponseLog::store((Object) $res_store);
                    }
                }
            }
            return $response;
        } catch(Exception $e) {
            Log::info($e);
            return null;
        }
    }

    public static function getComponents($client, $template, $client_parameters) {
        try {
            $client_parameters = array_change_key_case($client_parameters, CASE_LOWER);
            Log::info("Client Params");
            Log::info($client_parameters);
            $components = array();
            $header = $template->header;
            if(@$header) {
                $header_url = null;
                $params = self::getParameters(@$header['text']);
                if (@$template->header_url[0]) {
                    $header_url = $template->header_url[0];
                } else {
                    $url = TemplateConfig::getHeaderUrl($client['id'], $template);
                    if (@$url[0]) {
                        $header_url = $url[0];
                    }
                }
                $header_parameters = array();
                if (count($params) > 0) {
                    foreach($params as $k => $v) {
                        if (@$header['input_type'] == 1) { // Text
                            $param = array(
                                'parameters' => array(
                                    'type' => 'text',
                                    'text' => @$client_parameters[$v]
                                )
                            );
                        } else if (@$header['input_type'] == 2) { // Image
                            if (@$header['url']) {
                                $link = (@$client_parameters[$v]) ? $client_parameters[$v] : $header['url'];
                            }
                            $param = array(
                                'parameters' => array(
                                    'type' => 'image',
                                    'image'=> array(
                                        'link' => $link
                                    )
                                )
                            );
                        } else if (@$header['input_type'] == 3) { // Video
                            $param = array(
                                'parameters' => array(
                                    'type' => 'video'
                                )
                            );
                        } else if (@$header['input_type'] == 4) { // Document
                            $param = array(
                                'parameters' => array(
                                    'type' => 'document',
                                )
                            );
                        }
                        if (@$header['caption']) {
                            $param['parameters']['caption'] = $header['caption'];
                        }
                        if (@$header['file_name'] && @$header['input_type'] == 4) {
                            $param['parameters']['filename'] = $header['file_name'];
                        }
                        $header_parameters[] = $param;
                    }
                } else if ($header_url) {
                    if (@$header['input_type'] == 2) { // Image
                        $param = array(
                            'type' => 'image',
                            'image' => array(
                                'link' => @$header_url
                            )
                        );
                    } else if (@$header['input_type'] == 3) { // Video
                        $param = array(
                            'parameters' => array(
                                'type' => 'video'
                            )
                        );
                    } else if (@$header['input_type'] == 4) { // Document
                        $param = array(
                            'parameters' => array(
                                'type' => 'document',
                            )
                        );
                    }
                    $header_parameters[] = $param;
                }
                if (count($header_parameters) > 0) {
                    $components[] = array(
                        'type' => 'header',
                        'parameters' => $header_parameters
                    );
                }
            }
            $body = $template->template_content;
            if(@$body) {
                $params = self::getParameters($body);
                Log::info("Body Params");
                Log::info($params);
                if (count($params) > 0) {
                    $body_parameters = array();
                    foreach($params as $k => $v) {
                        $param = array(
                            'type' => 'text',
                            'text' => @$client_parameters[$v]
                        );
                        $body_parameters[] = $param;
                    }
                    if (count($body_parameters) > 0) {
                        $components[] = array(
                            'type' => 'body',
                            'parameters' => $body_parameters
                        );
                    }
                }
            }
            $footer = $template->footer;
            if(@$footer) {
                $footer_parameters = array();
                Log::info($footer);
                Log::info("Body Params");
                Log::info($footer_parameters);
                foreach($footer as $key => $value) {
                    if ($key == 'text') {
                        $params = self::getParameters($value);
                        if (count($params) > 0) {
                            foreach($params as $k => $v) {
                                $param = array(
                                    'type' => 'text',
                                    'text' => @$client_parameters[$v]
                                );
                                $footer_parameters = $param;
                            }
                        }
                    } else {
                        $params = self::getParameters($value['button_value']);
                        if (count($params) > 0) {
                            foreach($params as $k => $v) {
                                $param = array(
                                    'type' => 'button',
                                    'index' => $key,
                                    'sub_type' => @$value['sub_type'],
                                );
                                if (@$value['sub_type'] == 'url') {
                                    $param['parameters'][] = array(
                                        'type' => 'text',
                                        'text' => @$client_parameters[$v]
                                    );
                                } else if (@$value['sub_type'] == 'quick_reply') {
                                    $param['parameters'] = array(
                                        'type' => 'payload',
                                        'payload' => @$client_parameters[$v]
                                    );
                                } else if (@$value['sub_type'] == 'copy_code') {
                                    $param['parameters'] = array(
                                        'type' => 'coupon_code',
                                        'coupon_code' => @$client_parameters[$v]
                                    );
                                }
                                $footer_parameters = $param;
                                if (count($footer_parameters) > 0) {
                                    $components[] = $footer_parameters;
                                }
                            }
                        }
                    }
                }
            }
            return $components;
        } catch (Exception $e) {
            Log::info($e);
            return null;
        }
    }

    public static function replacePlaceholders($body, $placeholder_values, $db_name = null) {
        try {
            if ($db_name) {
                $placeholders = DB::table($db_name.".common_data")
                                ->where('type', '=', 'PLACHOLDER')
                                ->get();
            } else {
                $placeholders = CommonData::get('PLACHOLDER');
            }
            foreach($placeholders as $key => $value) {
                $body = str_replace("{{".Str::upper($value->description)."}}", @$placeholder_values[Str::lower($value->description)], $body);
            }
            return $body;
        }catch (Exception $e) {
            Log::info($e);
            return null;
        }
    }

    public static function getTemplate($template, $parameters = array(), $from_number, $to_number, $client_ref = null, $uuid = null) {
        try {
            $message_body = array();
            $message_body['to'] = $to_number;
            $message_body['from'] = $from_number;
            $message_body['channel'] = self::$channel;
            if ($client_ref) {
                $message_body['client_ref'] = $client_ref;
            }
            $message_body['webhook_url'] = self::$webhook_url;
            $message_body['webhook_version'] = self::$webhook_version;
            $whatsapp = array(
                'policy' => self::$policy,
                'locale' => self::$default_locale
            );
            $message_body['whatsapp'] = $whatsapp;
            if (@$template->template_type == 1) { // Approved
                $message_type = 'template';
                $header = @$template->header;
                $footer = @$template->footer;
                if ($body) $body = self::replacePlaceholder($body);
                $message_body['message_type'] = $message_type;
                $message_template = array(
                    'name' => @$template->template_key,
                    'parameters' => $parameters
                );
                if ($uuid) {
                    $message_body['context'] = array(
                        'message_uuid' => $uuid
                    );
                }
            } else if (@$template->template_type == 2) { // Custom Template
                $message_type = 'custom';
                
            }
            return $template;
        } catch(Exception $e) {
            Log::info($e);
            return null;
        }
    }

    public static function getTemplatesFromWhatsapp($waba_id) {
        try {
            $end_point = config('vonage.templates_endpoint');
            $end_point = str_replace('{WABAID}', $waba_id, $end_point);
            $jwt_token = self::generateJWT();
            $response = Http::withToken($jwt_token)->get($end_point);
            return $response->json();
        } catch (Exception $e) {
            Log::info($e);
            return null;
        }
    }

    public static function generateJWT($config = null) {
        try {
            $application_id = config('vonage.application_id');
            $private_key_path = '/app/keys/private.key';
            if ($config) {
                // client based implementation
                $application_id = $config->application_id;
                $private_key_path = $config->key_file;
            }
            Config::set('vonage.private_key', file_get_contents(storage_path().$private_key_path));
            Config::set('vonage.application_id', $application_id);
            $client = new Client(new Keypair(config('vonage.private_key'), new Application($application_id)));
            $jwt = $client->generateJwt();
            return @$jwt->toString();
        } catch(Exception $e) {
            Log::info($e);
            return null;
        }
    }

    public static function setClientConfig($config) {
        try {
            if ($config) {
                // client based implementation
                $application_id     = @$config->application_id;
                $private_key_path   = @$config->key_file;
                $api_key            = @$config->api_key;
                $api_secret         = @$config->api_secret;
                Config::set('vonage.application_id', $application_id);
                Config::set('vonage.private_key', file_get_contents(storage_path().'/app/'.$private_key_path));
                Config::set('vonage.api_key', $api_key);
                Config::set('vonage.api_secret', $api_secret);
            }
            return true;
        } catch(Exception $e) {
            Log::info($e);
            return null;
        }
    }

    public static function getParameters($body) {
        try {
            $parameters = array();
            preg_match_all('/\{\{[a-zA-Z0-9_]+\}\}/', $body, $params);
            if (@$params[0]) {
                foreach($params[0] as $key => $value) {
                    $value = str_replace("{{", "",$value);
                    $value = str_replace("}}", "",$value);
                    $parameters[] = Str::lower($value);
                }
            }
            return array_unique($parameters);
        } catch(Exception $e) {
            Log::info($e);
            return [];
        }
    }
}
?>