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
    public static function flag($name) {
        return self::getInstance()->internalFlag($name);
    }

    public static function printProfiler() {
        return self::getInstance()->internalPrint();
    }

    private function internalFlag($name) {
        $this->timing[] = microtime(true);
        $this->names[] = $name;
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