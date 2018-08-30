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
    
    private static $enable = false;
    private static $timers = [];

    public static function start($timer, $group = false) {
        self::$timers[$timer] = ['start' => microtime(true), 'group' => $group];
    }

    public static function stop($timer) {
        self::$timers[$timer]['stop'] = microtime(true);
    }

    public static function dump() {
        if(!self::$enable) return;
        $groups = [];
        echo "<pre>\n";
        foreach (self::$timers as $timer => $value) {
            echo "$timer:\n";
            echo "\t" . self::time($value['stop'] - $value['start']) . "\n";
            if($value['group']) {
                $groups[$value['group']] = $groups[$value['group']]??0;
                $groups[$value['group']] += ($value['stop'] - $value['start']);
            }
        }

        if($groups) {

            echo "=======================\n";
            echo "Groups\n";
            echo "=======================\n";

            foreach ($groups as $group => $time) {
                echo "$group:\n";
                echo "\t" . self::time($time) . "\n";

            }
        }

        echo "\n</pre>";
    }

    private static function time($time) {
        return number_format($time * 1000, 4) . "ms";
    }

    public static function setProfilerEnabled($state) {
        self::$enable = $state;
    }
}