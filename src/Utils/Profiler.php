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
    private static $file = false;
    private static $timers = [];

    public static function start($timer, $group = false, $tag = null) {
        self::$timers[$timer][] = ['start' => microtime(true), 'group' => $group, 'tag' => $tag];
    }

    public static function stop($timer) {
        $items = self::$timers[$timer];
        if(count($items) > 0) {
            $item = & self::$timers[$timer][count($items) - 1];
            $item['stop'] = microtime(true);
        }
    }

    public static function dump() {
        if(!self::$enable) return;
        $res = self::generate();
        if(self::$file) {
            file_put_contents(self::$file, $res, FILE_APPEND);
        } else {
            echo "<h1>Profiler Output</h1>";
            echo "<pre>\n";
            echo $res;
            echo "\n</pre>";
        }
    }

    public static function generate() {
        $groups = [];
        $res = "";
        foreach (self::$timers as $timer => $values) {
            $res .= "$timer:\n";
            foreach($values as $value) {
                $res .= "\t" . self::time($value['stop'] - $value['start']) . ($value['tag'] ?  " => " . $value['tag'] : ""). "\n";
                if($value['group']) {
                    $groups[$value['group']] = $groups[$value['group']]??0;
                    $groups[$value['group']] += ($value['stop'] - $value['start']);
                }
            }
        }

        if($groups) {

            $res .= "=======================\n";
            $res .= "Groups\n";
            $res .= "=======================\n";

            foreach ($groups as $group => $time) {
                $res .= "$group:\n";
                $res .= "\t" . self::time($time) . "\n";

            }
        }
        return  $res;
    }

    private static function time($time) {
        return number_format($time * 1000, 4) . "ms";
    }

    public static function setProfilerEnabled($state, $file = false) {
        self::$enable = $state;
    }
}