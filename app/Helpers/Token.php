<?php

namespace App\Helpers;

use Exception;
use App\Helpers\Date;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class Token {
    public static function encode($payload, $alg = 'HS256', $key = null) {
        try {
            $token_data = [];
            if (!$key) $key = config('app.jwt_key');
            $auth_token = md5(config('common.api_auth_code'));
            $token_data['auth_token'] = $auth_token;
            $payload = array_merge($token_data, $payload);
            $jwt = JWT::encode($payload, $key, $alg);
            return $jwt;
        } catch (Exception $e) {
            Log::info($e);
            return null;
        }
    }

    public static function decode($encoded, $alg = 'HS256', $key = null) {
        try {
            if (!$key) $key = config('app.jwt_key');
            $key = new Key($key, $alg);
            $data = JWT::decode($encoded, $key);
            return (array) $data;
        } catch (Exception $e) {
            Log::info($e);
            return null;
        }
    }

    public static function validate($token) {
        // Generate auth_token
        $auth_encode    = md5(config('common.api_auth_code'));
        $auth_token     = @$token['auth_token'];
        if($auth_encode == $auth_token) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public static function isExpired($token) {
        // Generate auth_token
        $expire_date = date('Y-m-d H:i:s', $token['expires_at']);
        // dd($expire_date, Carbon::now());
        $diff = Date::dateDiff($expire_date, Carbon::now(), 'hours');
        if ($expire_date > Carbon::now()) {
            return FALSE;
        } else {
            return TRUE;
        }
    }
}
?>