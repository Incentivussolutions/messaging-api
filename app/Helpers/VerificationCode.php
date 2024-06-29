<?php
    namespace App\Helpers;
    use Log;
    use Otp;

    class VerificationCode {
        protected $code_instance = null;
        public function __construct() {

        }

        /**
         * Generate OTP
         * 
         * @param string $identifier
         * @param integer $digits
         * @param integer $validity
         * @return string $otp
         */
        public static function generate(string $identifier, int $digits = 4, int $validity = 10) {
            $code_instance = new Otp;
            try {
                $otp = $code_instance->generate($identifier, $digits, $validity);
                if ($otp && @$otp->token) {
                    return $otp->token;
                } else {
                    return '';
                }
            } catch(Exception $e) {
                Log::info($e);
                return '';
            }
        }

        /**
         * Validate OTP
         * 
         * @param string $identifier
         * @param string $token
         * 
         * @return boolean $response
         */
        public static function validate(string $identifier, string $token) {
            $code_instance = new Otp;
            try {
                $response = $code_instance->validate($identifier, $token);
                if ($response && @$response->status) {
                    return $response->status;
                } else {
                    return false;
                }
            } catch(Exception $e) {
                Log::info($e);
                return false;
            }
        }
    }
?>