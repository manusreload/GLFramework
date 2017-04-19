<?php
/**
 * Created by PhpStorm.
 * User: mmunoz
 * Date: 4/04/17
 * Time: 9:11
 */

namespace GLFramework\Module;


use GLFramework\Bootstrap;

class ModuleScanner
{

    public function scan($base)
    {
        $list = array();
        $this->recursive($base, $list);
        $modules = array();
        foreach ($list as $moduleConfig)
        {
            $folder = dirname($moduleConfig);
            $config = Bootstrap::loadConfig($folder, "config.yml");
            $module = new Module($config, $folder);
            if($module->title)
            {
                $modules[] = $module;
            }
        }
        return $modules;
    }


    public function recursive($path, &$list = array())
    {
        $files = scandir($path);
        foreach ($files as $file)
        {
            if($file != "." && $file != "..")
            {
                $filename = $path . "/" . $file;
                if(is_dir($filename))
                {
                    $this->recursive($filename, $list);
                }
                else
                {
                    if($file == "config.yml")
                    {
                        $list[] = $filename;
                    }
                }
            }
        }
    }
}