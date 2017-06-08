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

    public static function parse($data)
    {
        if(function_exists('yaml_parse_file'))
        {
            $data = implode("\n", array_filter(explode("\n", $data), function($line)
            {
                if($line{0} === '#') return false;
                return true;
            }));
            return yaml_parse($data);
        }
        else
        {
            return \Symfony\Component\Yaml\Yaml::parse($data);
        }
    }
}