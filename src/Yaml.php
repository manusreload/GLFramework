<?php
/**
 * Created by PhpStorm.
 * User: mmunoz
 * Date: 8/06/17
 * Time: 13:22
 */

namespace GLFramework;


class Yaml extends SoftCache
{
    private static $instance;
    public function getType()
    {
        // TODO: Implement getType() method.
        return "yaml";
    }

    public static function getInstance() {
        if(self::$instance === null) {
            self::$instance = new Yaml();
        }
        return self::$instance;
    }


    private static $folder = false;
    public static function parse($file)
    {
        if(($res = self::getInstance()->preCache($file)) === false) {
            if(function_exists('yaml_parse_file'))
            {
                $data = file_get_contents($file);
                $data = implode("\n", array_filter(explode("\n", $data), function($line)
                {
                    $line = trim($line);
                    if($line && substr($line, 0, 1) === '#') return false;
                    return true;
                }));
                $res = \yaml_parse($data);
            }
            else
            {
                $res = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($file));
            }
            self::getInstance()->postCache($file, $res);
        }
        if($file && $res)
        {
            $res['_file'] = $file;
        }
        return $res;
    }

    public static function dump($data)
    {
        if(isset($data['_file']))
        {
            unset($data['_file']);
        }
        if(function_exists('yaml_emit'))
        {
            return yaml_emit($data);
        }
        else
        {
            return \Symfony\Component\Yaml\Yaml::dump($data);
        }
    }

//
//    private static function preCache($filename) {
//        if(!self::$folder) return false;
//        $stat = stat($filename);
//        $compiled = self::$folder . "/" . md5($filename) . ".php";
//        if(file_exists($compiled)) {
//            $config = include $compiled;
//            if($config['_hash']['mtime'] === $stat['mtime'] &&
//                $config['_hash']['size'] === $stat['size']) {
//                unset($config['_hash']);
//                return $config;
//            }
//        }
//        return false;
//    }
//
//    private static function postCache($filename, $config) {
//        if(!self::$folder) return false;
//        $stat = stat($filename);
//        $compiled = self::$folder . "/" . md5($filename) . ".php";
//        $config['_hash'] = $stat;
//        $data = "<?php return " . var_export($config, true) . ";\n// Generated from: $filename";
//        file_put_contents($compiled, $data);
//        unset($config['_hash']);
//    }
//
//    public static function setupCache($folder) {
//        if(!is_dir($folder)) {
//            mkdir($folder, 0777, true);
//        }
//        self::$folder = $folder;
//    }
}
