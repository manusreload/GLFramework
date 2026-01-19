<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 28/5/17
 * Time: 22:28
 */
namespace GLFramework\Resources;

use GLFramework\Bootstrap;
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
     * @param $asFile
     * @return string
     */
    public static function getResource($name, $module, $asFile)
    {
        $config = $module->getConfig();
        $folders = $config['app']['resources'];
        if (!is_array($folders)) {
            $folders = array($folders);
        }
        if (substr($name, 0, 1) == "/") {
            $name = substr($name, 1);
        }
        foreach ($folders as $folder) {
            $path = $module->getDirectory() . ($folder == ""?"":'/' . $folder) . '/' . $name;
            if (file_exists($path)) {
//                $path = realpath($path);
                if ($asFile) {
                    return $path;
                }
                if(substr($path, 0, 1) === ".") {
                    $path = substr($path, 1);
                }
                //$base = realpath($module->getDirectory());
                //$index = strpos($path, $base);
                return get_full_url($path);
            }
        }
        if($module !== ModuleManager::getInstance()->getMainModule()) {
            return self::getResource($name, ModuleManager::getInstance()->getMainModule(), $asFile);
        }
        return false;
    }
}
