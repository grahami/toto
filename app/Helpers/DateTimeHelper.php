<?php
/*
 * A helper class for date and time functionality
 */

namespace App\Helpers;

use Config;
use Session;


class DateTimeHelper
{
    public static function getIso8601ZuluTime($microseconds = false, $digits = 3)
    {
        $microTime = "";
        $microArray = explode(" ", microtime());
        if ($microseconds) {
            $microTime = "." . substr(sprintf("%.${digits}f", $microArray[0]), 2);
        }
        return gmdate("Y-m-d\TH:i:s", $microArray[1]) . $microTime . "Z";
    }

    public static function getTimeZone(){
        if (Session::has('timeZone')){
            $returnVal = Session::get('timeZone');
        } else {
            $returnVal = Config::get('app.timezone');
        }
        return $returnVal;
    }
    
}