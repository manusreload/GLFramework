<?php
/**
 * Created by PhpStorm.
 * User: mmunoz
 * Date: 4/04/17
 * Time: 9:11
 */

namespace GLFramework\Module;

use GLFramework\Bootstrap;

/**
 * Class ModuleScanner
 *
 * @package GLFramework\Module
 */
class ModuleScanner
{
    /**
     * TODO
     *
     * @param $base
     * @return array
     */
    public function scan($base)
    {
        $list = array();
        if (!is_array($base)) {
            $base = array($base);
        }
        $base[] = GL_INTERNAL_MODULES_PATH;
        foreach ($base as $folder) {
            $this->recursive($folder, $list);
        }
        $modules = array();
        foreach ($list as $moduleConfig) {
            $folder = dirname($moduleConfig);
            $config = Bootstrap::loadConfig($folder, 'config.yml');
            $module = new Module($config, $folder);
            if ($module->title && !$module->test) {
                $modules[] = $module;
            }
        }
        return $modules;
    }

    /**
     * TODO
     *
     * @param $path
     * @param array $list
     */
    public function recursive($path, &$list = array())
    {
        $files = scandir($path);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $filename = $path . '/' . $file;
                if (is_dir($filename)) {
                    $this->recursive($filename, $list);
                } else {
                    if ($file === 'config.yml' && !in_array($filename, $list)) {
                        $list[] = $filename;
                    }
                }
            }
        }
    }
}
