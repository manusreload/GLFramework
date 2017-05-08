<?php
/**
 *     GLFramework, small web application framework.
 *     Copyright (C) 2016.  Manuel Muñoz Rosa
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation, either version 3 of the License, or
 *     (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU General Public License for more details.
 *
 *     You should have received a copy of the GNU General Public License
 *     along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Created by PhpStorm.
 * User: manus
 * Date: 12/02/16
 * Time: 11:58
 */

namespace GLFramework\Module;


use GLFramework\Bootstrap;
use GLFramework\Controller\ErrorController;
use GLFramework\Controller\ExceptionController;
use GLFramework\Events;
use GLFramework\Filesystem;
use GLFramework\Log;
use GLFramework\Request;
use Symfony\Component\Yaml\Yaml;

class ModuleManager
{
    /**
     * @var Module[]
     */
    private $modules = array();
    private $config;
    private $directory;
    private $router;
    /**
     * @var Module
     */
    private $mainModule;
    private static $instance;
    private $runningModule;

    /**
     * ModuleManager constructor.
     * @param $config
     * @param $directory
     */
    public function __construct($config, $directory)
    {
        self::$instance = $this;
        $this->config = $config;
        $this->directory = $directory;
        $this->router = new \AltoRouter(array(), $this->config['app']['basepath']);
        $this->router->addMatchTypes(array(
            'idd' => '([0-9]+|add)?',
        ));
        $this->router->map('GET', "/_raw/[*:name]", array($this, 'handleFilesystem'), 'handleFilesystem');
    }

    public static function getInstance()
    {
        return self::$instance;
    }

    /**
     * @param $name
     * @return Module
     */
    public static function getModuleInstanceByName($name)
    {
        $instance = self::getInstance();
        foreach($instance->getModules() as $module)
        {
            if($module->title == $name) return $module;
        }
    }

    public static function exists($string)
    {
        $instance = self::getInstance();
        foreach($instance->getModules() as $module)
        {
            if($module->title == $string) return true;
        }
        return false;
    }

    public static function existsController($key)
    {
        $instance = self::getInstance();
        foreach($instance->getModules() as $module)
        {
            foreach($module->getControllers() as $controller => $file)
            {
                if($key == $controller) return true;
            }
        }
        return false;
    }

    /**
     * @param $key
     * @return bool|\GLFramework\Controller
     */
    public static function instanceController($key)
    {
        $instance = self::getInstance();
        foreach($instance->getModules() as $module)
        {
            foreach($module->getControllers() as $controller => $file)
            {
                if($key == $controller)
                {
                    return $module->instanceController($controller);
                }
            }
        }
        return false;
    }

    /**
     * @param $key
     * @return bool|Module
     */
    public static function getModuleForController($key)
    {
        $instance = self::getInstance();
        foreach($instance->getModules() as $module)
        {
            foreach($module->getControllers() as $controller => $file)
            {
                if($key == $controller)
                {
                    return $module;
                }
            }
        }
        return false;
    }

    /**
     * @return \AltoRouter
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @return Module[]
     */
    public function getModules()
    {
        return $this->modules;
    }

    /**
     * @return Module
     */
    public function getMainModule()
    {
        return $this->mainModule;
    }

    /**
     * @param $module Module
     * @return array
     */
    public function getViews($module = false)
    {
        $views = array();
        if($module) $views = $module->getViews();
        foreach($this->getModules() as $module2)
        {
            if($module2 != $module)
            {
                foreach($module2->getViews() as $view)
                {
                    $views[] = $view;
                }
            }
        }
        $mainModule = ModuleManager::getInstance()->getMainModule();
        if($module != $mainModule) Module::addFolder($views, $mainModule->getViews());
        // Add framework views
        Module::addFolder($views, realpath(__DIR__ . "/../../..") . "/");
        Module::addFolder($views, realpath(__DIR__ . "/../..") . "/");
        Module::addFolder($views, realpath(__DIR__ . "/../..") . "/views");
        Module::addFolder($views, realpath(__DIR__ . "/../..") . "/modules");
        return $views;
    }

    public function init()
    {
        $time = microtime();
        $module = $this->load($this->directory, array(), true);
        if($module)
        {
            $this->mainModule = $module;
            $this->add($module);
            if(isset($this->config['modules']))
            {
                $this->loadConfig($this->config);
            }

            foreach($this->modules as $module)
            {
                $module->init();
                $module->register_router($this->router);
            }
            foreach($this->modules as $module)
            {
                $module->register_events();
            }
        }
        else
        {
            throw new \Exception("Can't not load the main module!");
        }
    }

    public function loadConfig($config)
    {
        $modules = $config['modules'];
        if(!is_array($modules)) $modules = array($modules);
        foreach($modules as $subsection => $value)
        {
            $dirbase = $this->directory;
            if((string) $subsection == "internal")
            {
                $dirbase = __DIR__ . "/../../modules";
            }
            else if(is_numeric($subsection))
            {
                $dirbase .= "modules";
            }
            else
            {
                $dirbase .= "/$subsection";
            }
            if($value)
            {
                if(!is_array($value)) $value = array($value);
                foreach($value as $name => $extra)
                {
                    if(empty($extra)) continue;
                    if(is_integer($name)) $name = $extra;
                    if(is_array($extra))
                    {
                        $name = key($extra);
                        $extra = current($extra);
                    }
                    Log::d("Loading: " . $dirbase . "/" . $name);
                    $module = $this->load($dirbase . "/" . $name, $extra);
                    if($module)
                    {
                        if(!$this->exists($module->title))
                        {
                            $this->add($module);
                            $this->loadModuleDependencies($module);
                        }
                    }
                    else{
                        throw new \Exception("Can't not load module: " . $name . " in directory: '" . $dirbase . "'" );
                    }

                }
            }
        }
    }

    public function load($folder, $extra = null, $main = false)
    {
        $configFile = $folder . "/config.yml";
        if(file_exists($configFile))
        {
            $config = Bootstrap::loadConfig($folder, "config.yml");
        }
        else
        {
            if(!$main) return null;
            $config = Bootstrap::getSingleton()->getConfig();
            if(!$config)
            {
                return null;
            }
        }
        if(is_array($extra))
        {
            $config = array_merge_recursive_ex($config, $extra);
        }
        return new Module($config, $folder);
    }

    /**
     * @param $module Module
     */
    public function loadModuleDependencies($module)
    {
        $config = $module->getConfig();
        if(isset($config['modules']))
        {
            $this->loadConfig($config);
        }
    }

    /**
     * @param $module Module
     */
    public function add($module)
    {
        if($module != null)
            $this->modules[] = $module;
    }

    public function isEnabled($name)
    {
        foreach($this->modules as $module)
        {
            if($name == $module->title) return true;
        }
        return false;
    }


    /**
     * @param null $url
     * @param null $method
     * @return \GLFramework\Response
     */
    public function run($url = null, $method = null)
    {
        $request = new Request($method, $url);
        try {

            if($match = $this->router->match($url, $method))
            {
                if($match['name'] == "handleFilesystem")
                {
                    return $this->handleFilesystem($match['params'], $request);
                }
                else
                {
                    $target = $match['target'];
                    $module = $target[0];
                    $this->runningModule = $module;
                    $controller = $target[1];
                    $request->setParams($match['params']);
                    return $module->run($controller, $request);
                }
            }
            else
            {
                return $this->mainModule->run(new ErrorController("Controller for '$url' not found. " . $this->getRoutes()), $request);
            }
        } catch (\Exception $ex) {
            Events::fire('onException', $ex);
            return $this->mainModule->run(new ExceptionController($ex), $request);
        } catch (\Throwable $ex) {
            Events::fire('onError', $ex);
            return $this->mainModule->run(new ExceptionController($ex), $request);
        }
        return false;
    }
    
    public function getRoutes()
    {
        if($this->config['app']['debug'])
        {
            $html = "<pre>";
            $result = array();
            foreach($this->modules as $module)
            {
                $list = $module->getControllersUrlRoutes();
                $result = array_merge($list, $result);
            }
            $html .= implode("\n", $result);
            $html .= "</pre>";
            return $html;
        }
    }


    public function handleFilesystem($args, $request)
    {
        $file = urldecode($args['name']);
        $filesystem = new Filesystem($file);
        if($filesystem->exists())
        {
            if($filesystem->isPublic())
            {
                header("Content-Type: " . mime_content_type($filesystem->getAbsolutePath()));
                $filesystem->read(true);
                die();
            }
            else
            {
                return $this->mainModule->run(new ErrorController("Este archivo no es descargable"), $request);
            }
        }
        else
        {
            return $this->mainModule->run(new ErrorController("No se ha encontrado este archivo!"), $request);
        }
    }

    /**
     * @return mixed
     */
    public function getRunningModule()
    {
        return $this->runningModule;
    }


}