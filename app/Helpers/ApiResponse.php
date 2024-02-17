<?php

    namespace App\Helpers;
    use Illuminate\Support\Facades\App;
    use Illuminate\Support\Facades\Lang;

    class ApiResponse {
        protected static $http_responses = array(
            '200' => array(
                'success' => true,
                'data' => null,
                'message' => 'Success'
            ),
            '201' => array(
                'success' => true,
                'data' => null,
                'message' => 'Created'
            ),
            '202' => array(
                'success' => true,
                'data' => null,
                'message' => 'Accepted'
            ),
            '203' => array(
                'success' => false,
                'data' => null,
                'message' => 'Non-Authoritative Information'
            ),
            '400' => array(
                'success' => false,
                'data' => null,
                'message' => 'Bad Request'
            ),
            '401' => array(
                'success' => false,
                'data' => null,
                'message' => 'Unauthorized'
            ),
            '402' => array(
                'success' => false,
                'data' => null,
                'message' => 'Forbidden'
            ),
            '404' => array(
                'success' => false,
                'data' => null,
                'message' => 'Not Found'
            ),
            '405' => array(
                'success' => false,
                'data' => null,
                'message' => 'Method Not Allowed'
            ),
            '422' => array(
                'success' => false,
                'data' => null,
                'message' => 'Unprocessable Entitiy'
            ),
            '429' => array(
                'success' => false,
                'data' => null,
                'message' => 'Too many requests'
            ),
            '500' => array(
                'success' => false,
                'data' => null,
                'message' => 'Internal Server Error'
            ),
        );

        protected static $api_content_type = 'application/json';

        public static function send($httpcode, $data = null, $message = null, $is_validator = false) {
            $response = self::$http_responses[$httpcode];
            if ($data) {
                $response['data'] = $data;
            }
            if ($message) {
                $response['message'] = $message;
            }
            if (!$is_validator) {
                $response['message'] = __('messages.'.$response['message']);
            }
            // $response['label_locale'] = Lang::get('labels');
            return response($response, $httpcode)->header('Content-Type', self::$api_content_type);
        }
    }
?>