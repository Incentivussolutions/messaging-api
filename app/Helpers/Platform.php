<?php

namespace App\Helpers;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Browser;


class Platform {
    public static $device_details = array(
        'user_agent' => null,
        'ip' => null,
        'device_type' => null,
        'browser' => null,
        'browser_version' => null,
        'platform' => null,
        'platform_family' => null,
        'platform_version' => null
    );

    public static function getUserAgent() {
        $user_agent = Browser::userAgent();
        return $user_agent;
    }

    public static function getIP() {
        return @$_SERVER['HTTP_X_FORWARDED_FOR'] ? @$_SERVER['HTTP_X_FORWARDED_FOR'] : request()->ip();
    }
    
    public static function getDeviceType() {
        $device_type = null;
        if (Browser::isDesktop()) {
            $device_type = 'Desktop/Laptop';
        } else if (Browser::isTablet()) {
            $device_type = 'Tablet';
        } else if (Browser::isMobile() || Browser::isInApp()) {
            $device_type = 'Mobile';
        } else if (Browser::isBot()) {
            $device_type = 'Bot';
        }
        return $device_type;
    }
    

    public static function getBrowser() {
        $browser = array(
            'browser' => 'unknown',
            'version' => 'unknown'
        );
        $browser_type = null;
        if (Browser::isChrome()) {
            $browser_type = 'Chrome';
        } else if (Browser::isFirefox()) {
            $browser_type = 'Firefox';
            $browser['browser'] = $browser_type;
            $browser['version'] = Browser::browserVersion();
        } else if (Browser::isOpera()) {
            $browser_type = 'Opera';
        } else if (Browser::isSafari()) {
            $browser_type = 'Safari';
        } else if (Browser::isEdge()) {
            $browser_type = 'Edge';
        } else if (Browser::isInApp()) {
            $browser_type = 'InApp';
        } else if (Browser::isIE()) {
            $browser_type = 'IE';
        }
        if ($browser_type) {
            $browser['browser'] = $browser_type;
            $browser['version'] = Browser::browserVersion();
        }
        return $browser;
    }

    public static function getPlatform() {
        $platform = array(
            'platform' => 'unknown',
            'version' => 'unknown'
        );
        $platform_type = null;
        if (Browser::isWindows()) {
            $platform_type = 'Windows';
        } else if (Browser::isLinux()) {
            $platform_type = 'Linux';
        } else if (Browser::isMac()) {
            $platform_type = 'Mac';
        } else if (Browser::isAndroid()) {
            $platform_type = 'Android';
        }
        if ($platform_type) {
            $platform['browser'] = $platform_type;
            $platform['version'] = Browser::platformVersion();
        }
        return $platform;
    }
    public static function getDeviceDetails($is_ignore = false) {
        $response                                   = Browser::detect();
        $response                                   = $response ? $response->toArray() : [];
        $user_agent                                 = self::getUserAgent();
        $ip                                         = self::getIP();
        $device_type                                = self::getDeviceType();
        $browser                                    = self::getBrowser();
        $platform                                   = self::getPlatform();
        self::$device_details['user_agent']         = $user_agent;
        self::$device_details['ip']                 = $ip;
        self::$device_details['device_type']        = $device_type;
        self::$device_details['browser']            = $browser['browser'];
        self::$device_details['browser_version']    = $browser['version'];
        self::$device_details['platform']           = $platform['platform'];
        self::$device_details['platform_version']   = $platform['version'];
        $response                                   = array_merge($response, self::$device_details);
        // Array index camel_case change
        $tmp = [];
        foreach ($response as $key => $value) {
            if ($key === 'isIE') $key = 'is_ie';
            $tmp[Str::snake($key)] = $value;
        }
        $response = $tmp;
        // ignore except parameters
        if ($is_ignore) {
            $except_params = ['user_agent', 'is_mobile', 'is_tablet', 'is_desktop', 'is_bot', 'is_chrome', 'is_firefox', 'is_opera', 'is_safari', 'is_edge', 'is_in_app', 'is_ie', 'is_windows', 'is_linux', 'is_mac', 'is_android'];
            $response = Arr::except($response, $except_params);
        }
        return $response;
    }
}
?>