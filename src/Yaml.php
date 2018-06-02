<?php
/**
 * Created by PhpStorm.
 * User: mmunoz
 * Date: 8/06/17
 * Time: 13:22
 */

namespace GLFramework;


class Yaml
{

    public static function parse($file)
    {
        if(function_exists('yaml_parse_file'))
        {
            $data = file_get_contents($file);
            $data = implode("\n", array_filter(explode("\n", $data), function($line)
            {
                $line = trim($line);
                if($line && $line{0} === '#') return false;
                return true;
            }));
            $res = yaml_parse($data);
        }
        else
        {
            $res = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($file));
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
}