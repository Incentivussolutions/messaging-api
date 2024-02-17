<?php

namespace App\Helpers;

use Carbon\Carbon;

class Date {
    public static $date_format = 'Y-m-d';
    public static $time_format = 'H:i:s';
    public static $datetime_format = 'Y-m-d H:i:s';
    public static $human_readable = 'd-m-Y';
    public static $acceptable_formats = array(
        'Y-m-d',
        'H:i:s',
        'Y-m-d H:i:s',
        'd-m-Y'
    );
    public static $acceptable_metrices = array(
        'millennium',
        'century',
        'decade',
        'year',
        'quarter',
        'month',
        'week',
        'weekday',
        'day',
        'hour',
        'minute',
        'second',
        'millisecond',
        'microsecond'
    );
    public static $metrices_allow_overflow = array(
        'year',
        'month'
    );
    public static $date_diff_metrices = array(
        'milliseconds',
        'seconds',
        'hours',
        'days',
        'weeks',
        'months',
        'years'
    );

    public function __construct() {

    }

    public static function getDate($date = null, $is_human_readable = false) {
        $timezone = config('app.timezone');
        $date = $date ? Carbon::parse($date) : Carbon::now();
        $format = self::$date_format;
        if ($is_human_readable === true) {
            $format = self::$human_readable;
        }
        return $date->timezone($timezone)->format($format);
    }

    public static function getTime($date = null) {
        $timezone = config('app.timezone');
        $date = $date ? Carbon::parse($date) : Carbon::now();
        return $date->timezone($timezone)->format(self::$time_format);
    }

    public static function getDateTime($date = null) {
        $timezone = config('app.timezone');
        $date = $date ? Carbon::parse($date) : Carbon::now();
        return $date->timezone($timezone)->format(self::$datetime_format);
    }

    public static function add($date, $metric, $value, $format, $with_over_flow = false) {
        if (!in_array($metric, self::$acceptable_metrices)) {
            return null;
        }
        $timezone = config('app.timezone');
        if ($with_over_flow == false && !in_array($metric, self::$metrices_allow_overflow)) {
            $with_over_flow = true;
        }
        if ($date) {
            $date = Carbon::parse($date);
            if ($with_over_flow) {
                $date = $date->add($value, $metric);
            } else {
                if ($metric == 'month') {
                    $date = $date->addMonthsNoOverflow($value);
                } else if ($metric == 'year') {
                    $date = $date->addYearsNoOverflow($value);
                }
            }
            if ($format == null || !in_array($format, self::$acceptable_formats)) {
                $format = self::$date_format;
            }
            return $date->timezone($timezone)->format($format);
        } else {
            return null;
        }
    }

    public static function dateDiff($from, $to, $metric = 'seconds') {
        if (!in_array($metric, self::$date_diff_metrices)) {
            $metric = 'seconds';
        }
        $from = Carbon::parse($from);
        $to = Carbon::parse($to);
        $diff = 0;
        switch($metric) {
            case 'milliseconds':
                return $from->floatDiffInSeconds($to) * 1000;
            case 'seconds':
                return $from->flotDiffInSeconds($to);
            case 'minutes':
                return $from->floatDiffInMinutes($to);
            case 'hours':
                return $from->floatDiffInHours($to);
            case 'days':
                return $from->floatDiffInDays($to);
            case 'weeks':
                return $from->floatDiffInWeeks($to);
            case 'months':
                return $from->floatDiffInMonths($to);
            case 'months':
                return $from->floatDiffInYears($to);
            default:
                return null;
        }
    }
    
}

?>