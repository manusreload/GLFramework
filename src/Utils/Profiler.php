<?php
/**
 * Created by PhpStorm.
 * User: mmunoz
 * Date: 29/08/2018
 * Time: 15:45
 */

namespace GLFramework\Utils;


class Profiler
{
    
    private static $enable = true;
    private static $timers = [];

    public static function start($timer, $label = false) {
        self::$timers[$timer] = ['start' => microtime(true), 'label' => $label];
    }

    public static function stop($timer) {
        self::$timers[$timer]['stop'] = microtime(true);
    }

    public static function dump() {
        if(!self::$enable) return;
        foreach (self::$timers as $timer => $value) {
            echo "$timer:\n";
            echo "\t" . self::time($value['stop'] - $value['start']) . "\n";
        }
    }

    private static function time($time) {
        return number_format($time * 1000, 4) . "ms";
    }
}