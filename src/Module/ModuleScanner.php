<?php
/**
 * Created by PhpStorm.
 * User: mmunoz
 * Date: 4/04/17
 * Time: 9:11
 */

namespace GLFramework\Module;

use GLFramework\Bootstrap;
use GLFramework\Utils\Profiler;

/**
 * Class ModuleScanner
 *
 * @package GLFramework\Module
 */
class ModuleScanner
{
    private $commonDirectories = ['config', 'filesystem', 'model', 'pages', 'view', 'vendor'];
    /**
     * TODO
     *
     * @param $base
     * @return array
     */
    public function scan($base, $all = false)
    {
        Profiler::start('scan', 'ModuleScanner');
        $list = array();
        if (!is_array($base)) {
            $base = array($base);
        }
        $base[] = Bootstrap::getSingleton()->relative(GL_INTERNAL_MODULES_PATH);
        foreach ($base as $folder) {
            $this->recursive($folder, $list, $all?-1:4);
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
        Profiler::stop('scan');
        return $modules;
    }

    /**
     * TODO
     *
     * @param $path
     * @param array $list
     */
    public function recursive($path, &$list = array(), $limit = 4)
    {
        if(!$path) return;
        if($limit == 0) return;
        $files = scandir($path);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..' && !$this->isCommonDirectory($file)) {
                $filename = $path . '/' . $file;
                if (is_dir($filename)) {
                    $this->recursive($filename, $list, $limit - 1);
                } else {
                    if ($file === 'config.yml' && !in_array($filename, $list)) {
                        $list[] = $filename;
                    }
                }
            }
        }
    }

    private function isCommonDirectory($name) {
        return in_array($name, $this->commonDirectories);
    }
}
