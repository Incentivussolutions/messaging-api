<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Helpers\Date;
use App\Helpers\Token;
use App\Models\Client;
use App\Models\Device;
use App\Helpers\Common;
use App\Helpers\Platform;
use App\Models\CommonData;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\AppAccessToken;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class ApplicationController extends Controller
{
    /**
     * Get Application Common Token
     * @param Request $request
     * @param Response $response
     */
    public function getCommonToken(Request $request) {
        try {
            $response = [];
            $device_details = Platform::getDeviceDetails();
            // $device = Device::store($request, $device_details);
            if ($device_details) {
                $token_data = array(
                    'device_details' => $device_details,
                    'expires_at' => strtotime(Date::add(Carbon::now(), 'day', 7, 'Y-m-d H:i:s'))
                );
                $token = Token::encode($token_data);
                if ($token) {
                    $response['token'] = $token;
                    return ApiResponse::send(200, $response, "Token generated");
                }
            }
            return ApiResponse::send(203);
        } catch(Exception $e) {
            Log::info($e);
            return ApiResponse::send(500);
        }
    }

    /**
     * Application Login
     * @param Request $request
     * @param Response $response
     */
    public function login(Request $request) {
        try {
            $response = [];
            // Validation given Data's
            $validator = array(
                'email'         => ['string', 'required'],
                'password'      => ['string', 'required']
            );
            $validator = Common::validator($request, $validator);
            if ($validator && $validator->fails()) {
                return ApiResponse::send(203, null, $validator->errors()->first(), true);
            }
            $client = Client::find(@$request->client['id']);
            if ($client) {
                Common::changeClient($client->unique_id);
                $response = User::login($request);
            }
            if ($response) {
                $response = Arr::except($response, ['created_user_id', 'updated_user_id', 'deleted_user_id', 'created_at', 'updated_at', 'deleted_at']);
                $refresh_token_data = array(
                    'user' => $response,
                    'client' => $client,
                    'is_refresh' => true,
                    'expires_at' => strtotime(Date::add(Carbon::now(), 'day', 7, 'Y-m-d H:i:s'))
                );
                $refresh_token  = Token::encode($refresh_token_data);
                if ($refresh_token) {
                    AppAccessToken::where('id', '=', $request->access_id)->delete();
                    $app_access_token                   = new AppAccessToken;
                    $app_access_token->user_id          = $response->id;
                    $app_access_token->refresh_token    = $refresh_token;
                    $app_access_token->save();
                    $token_data = array(
                        'user' => $response,
                        'client' => $client,
                        'access_id' => $app_access_token->id,
                        'expires_at' => strtotime(Date::add(Carbon::now(), 'hour', 8, 'Y-m-d H:i:s'))
                    );
                    $token          = Token::encode($token_data);
                    $app_access_token->access_token     = $token;
                    $app_access_token->save();
                    $response['token'] = $token;
                    $response['refresh_token'] = $refresh_token;
                    return ApiResponse::send(200, $response, "User logged in successfully");
                }
            }
            return ApiResponse::send(203);
        } catch(Exception $e) {
            Log::info($e);
            return ApiResponse::send(500);
        }
    }

    /**
     * Application Refresh Token
     * @param Request $request
     * @param Response $response
     */
    public function refreshToken(Request $request) {
        try {
            $response = [];
            // Validation given Data's
            $validator = array(
                'refresh_token' => ['string', 'required']
            );
            $validator = Common::validator($request, $validator);
            if ($validator && $validator->fails()) {
                return ApiResponse::send(203, null, $validator->errors()->first(), true);
            }
            $response = User::first($request->user);
            $last_active_token = AppAccessToken::where('refresh_token', '=', $request->refresh_token)->where('is_active', '=', 1)->first();
            if ($response && $last_active_token) {
                $last_active_token  = AppAccessToken::find($last_active_token->id);
                $refresh_token      = $last_active_token->refresh_token;
                AppAccessToken::where('refresh_token', '=', $request->refresh_token)->update(['is_active' => false]);
                $new_token                   = new AppAccessToken;
                $new_token->user_id          = $response->id;
                $new_token->refresh_token    = $refresh_token;
                $new_token->save();
                $token_data         = array(
                    'user' => $response,
                    'access_id' => $new_token->id,
                    'expires_at' => strtotime(Date::add(Carbon::now(), 'hour', 8, 'Y-m-d H:i:s'))
                );
                $token                       = Token::encode($token_data);
                $new_token->access_token     = $token;
                $new_token->save();
                $res['token'] = $token;
                $res['refresh_token'] = $refresh_token;
                return ApiResponse::send(200, $res, "Refresh token");
            } else {
                return ApiResponse::send(203, null, "Error fetching refresh token");
            }
        } catch(Exception $e) {
            Log::info($e);
            return ApiResponse::send(500);
        }
    }

    /**
     * Application Log out
     * @param Request $request
     * @param Response $response
     */
    public function logout(Request $request) {
        try {
            $response = [];
            // Validation given Data's
            // $validator = array(
            // );
            // $validator = Common::validator($request, $validator);
            // if ($validator && $validator->fails()) {
            //     return ApiResponse::send(203, null, $validator->errors()->first(), true);
            // }
            if (AppAccessToken::where('id', '=', $request->access_id)->delete()) {
                return ApiResponse::send(200, null, 'Logged out successfully');
            } else {
                return ApiResponse::send(203);
            }
        } catch(Exception $e) {
            Log::info($e);
            return ApiResponse::send(500);
        }
    }

    // Common Dropdown function

    public function getDropdownList(Request $request) {
        $response = null;
        try {
            $validator = array(
                'type' => ['required', 'array']
            );
            $validator = Common::validator($request, $validator);
            if ($validator && $validator->fails()) {
                return ApiResponse::send(203, null, $validator->errors()->first(), true);
            }
            foreach($request->type as $key => $value) {
                $type = Str::upper($value);
                $list = CommonData::get($type, $request->all());
                $response[Str::plural(Str::lower($value))] = ($list) ? $list : [];
            }
            if ($response) {
                return ApiResponse::send(200, $response, "List data");
            }
            return ApiResponse::send(203);
        } catch(Exception $e) {
            Log::info($e);
            return ApiResponse::send(500);
        }
    }

    public function clientSearch(Request $request) {
        $response = null;
        try {
            $validator = array(
                'client_name' => ['required', 'string']
            );
            $validator = Common::validator($request, $validator);
            if ($validator && $validator->fails()) {
                return ApiResponse::send(203, null, $validator->errors()->first(), true);
            }
            $response = Client::search($request);
            if ($response) {
                return ApiResponse::send(200, $response, "client details");
            }
            return ApiResponse::send(203);
        } catch(Exception $e) {
            Log::info($e);
            return ApiResponse::send(500);
        }
    }

    public function getAuthKey(Request $request) {
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
            Common::changeClient();
            $data = Client::getClientByAuthKey($request->client['authkey']);
            if ($data) {
                $response['auth_key'] = $data->authkey;
                return ApiResponse::send(200, $response, "Auth Key");
            } else {
                return ApiResponse::send(203);
            }
        } catch (Exception $e) {
            Log::info($e);
            return null;
        }
    }
}
