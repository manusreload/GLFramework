<?php
/**
 * Created by PhpStorm.
 * User: mmunoz
 * Date: 1/10/18
 * Time: 16:47
 */

namespace GLFramework;


abstract class SoftCache
{
    private static $folder;
    private static $ingone = [];
    public static function setup($folder) {
        self::$folder = $folder;
    }

    public static function ignoreCacheForType($type) {
        self::$ingone[] = $type;
    }

    abstract public function getType();

    public function isCacheEnabled() {

        return !!self::$folder && !in_array($this->getType(), self::$ingone);
    }
    protected function getCacheFolder() {
        $folder = self::$folder . "/" . $this->getType();
        if(!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }
        return $folder;
    }

    /**
     * @param $filename
     * @return bool|array
     */
    public function preCache($filename) {
        if(!$this->isCacheEnabled()) return false;
        $stat = stat($filename);
        $compiled = $this->getCacheFolder() . "/" . md5($filename) . ".php";
        if(file_exists($compiled)) {
            $config = include $compiled;
            if($config['_hash']['mtime'] === $stat['mtime'] &&
                $config['_hash']['size'] === $stat['size']) {
                unset($config['_hash']);
                return $config;
            }
        }
        return false;
    }

    /**
     * @param $filename
     * @param $data array
     * @return bool
     */
    public function postCache($filename, $data) {
        if(!$this->isCacheEnabled()) return false;
        if(!is_array($data)) return false;
        $stat = stat($filename);
        $compiled = $this->getCacheFolder() . "/" . md5($filename) . ".php";
        $data['_hash'] = $stat;
        $date = date("Y-m-d H:i:s");
        $string = "<?php \n\n /**\n* Generated from: $filename\n* Date $date\n**/\n return " . var_export($data, true) . ";\n";
        file_put_contents($compiled, $string);
        unset($data['_hash']);
    }

}