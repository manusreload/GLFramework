<?php
/**
 * Created by PhpStorm.
 * User: mmunoz
 * Date: 24/07/18
 * Time: 12:58
 */

namespace GLFramework\Utils;


class Profiler
{

    private static $instance;
    private $timing = [];
    private $names = [];
    public static function getInstance() {
        if(self::$instance == null) {
            self::$instance = new Profiler();
        }
        return self::$instance;
    }
    public static function flag($path) {
        return self::getInstance()->internalFlag(func_get_args());
    }

    public static function timer(... $path) {
        return ['path' => $path, 'time' => microtime(true)];
    }


    public static function printProfiler() {
        return self::getInstance()->internalPrint();
    }

    private function internalFlag($path) {
        if(!is_array($path))
        {
            $path = [$path];
        }
        $this->timing[] = microtime(true);
        $this->names[] = implode("::", $path);
    }

    private function internalPrint() {
        $result = "";
        $size = count($this->timing);
        for($i=0;$i<$size - 1; $i++)
        {
            $result .= "{$this->names[$i]}\n";
            $result .= sprintf("\t%f ms\n", ($this->timing[$i+1]-$this->timing[$i]) * 1000);
        }
        $result .= "{$this->names[$size-1]}\n";

        return $result;
    }
}