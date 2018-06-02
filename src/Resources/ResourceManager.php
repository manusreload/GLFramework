<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 28/5/17
 * Time: 22:28
 */

namespace GLFramework\Resources;


use GLFramework\Module\Module;
use GLFramework\Module\ModuleManager;

class ResourceManager
{

    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * ResourceManager constructor.
     * @param ModuleManager $moduleManager
     */
    public function __construct(ModuleManager $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }

    public function checkResources()
    {
        return false;
    }

    /**
     * TODO
     * @param $name
     * @param $module Module
     * @return string
     */
    public static function getResource($name, $module)
    {
        $config = $module->getConfig();
        $folders = $config['app']['resources'];
        if (!is_array($folders)) {
            $folders = array($folders);
        }
        foreach ($folders as $folder) {
            $path = $module->getDirectory() . '/' . $folder . '/' . $name;
            if (file_exists($path)) {
                die($path);
                $path = realpath($path);
                $base = dirname($_SERVER['SCRIPT_FILENAME']);
                $index = strpos($path, $base);
                $url = substr($path, $index + strlen($base));
                $protocol = 'http';
                if (strpos($_SERVER['SCRIPT_URI'], 'https') !== false) {
                    $protocol = 'https';
                }

                return $protocol . '://' . $_SERVER['HTTP_HOST'] . $url;
            }
            else
            {
                die("OMG! $name");
            }
        }
    }
}
