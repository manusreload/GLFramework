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
use GLFramework\Controller;
use GLFramework\Cron\CronTask;
use GLFramework\Event\Event;
use GLFramework\Events;
use GLFramework\Log;
use GLFramework\Request;
use GLFramework\SoftCache;
use GLFramework\Utils\Profiler;
use GLFramework\View;
define('ALLOW_USER', 'allow');
define('DISALLOW_USER', 'disallow');
/**
 * Class Module
 *
 * @package GLFramework\Module
 */
class Module extends SoftCache
{

    public $title;
    public $description;
    public $version;
    public $test;
    public $modelNamespace;
    private $config;
    private $directory;
    private $settings = array();
    /**
     * Para registrar una tarea en cron, el modulo tiene que definir que ejecutar.
     * @var array
     */
    private $cron = array();

    private $controllers = array();
    private $controllers_map = array();
    private $controllers_routes = array();
    private $controllers_url_routes = array();
    private $events = array();
    private $router;
    private $spl_autoload_controllers;
    private $spl_autoload_models;

    static $routes = [];
    /**
     * Module constructor.
     * @param $config
     * @param $directory
     */
    public function __construct($config, $directory)
    {
        $this->config = $config;
        $this->directory = $directory;
        if (isset($this->config['title'])) {
            $this->title = $this->config['title'];
        }
        if (isset($this->config['model_namespace'])) {
            $this->modelNamespace = $this->config['model_namespace'];
        } else {
            $this->modelNamespace = $this->title;
        }
        if (isset($this->config['description'])) {
            $this->description = $this->config['description'];
        }
        if (isset($this->config['version'])) {
            $this->version = $this->config['version'];
        }
        if (isset($this->config['test'])) {
            $this->test = $this->config['test'];
        }

        if (isset($this->config['app']['settings'])) {
            $settings = $this->config['app']['settings'];
            foreach ($settings as $name => $setting) {
                $moduleSetting = new ModuleSettings();
                $moduleSetting->description = isset($setting['description'])?$setting['description']:"";
                $moduleSetting->type = isset($setting['type'])?$setting['type']:"";
                $moduleSetting->default = isset($setting['default'])?$setting['default']:"";
                $moduleSetting->key = $name;
                $this->settings[] = $moduleSetting;
            }
        }
        if (isset($this->config['app']['cron'])) {
            $cron = $this->config['app']['cron'];
            if (!is_array($cron)) {
                $cron = array($cron);
            }

            foreach ($cron as $title => $fn) {
                $this->cron[] = new CronTask($this, $title, $fn);
            }
        }
        //        $this->config = array_merge_recursive_ex($this->config, Bootstrap::getSingleton()->getConfig());
    }

    public function getType()
    {
        // TODO: Implement getType() method.
        return "module";
    }


    /**
     * TODO
     *
     * @param $array
     * @param $folder
     */
    public static function addFolder(&$array, $folder)
    {
        if (is_array($folder)) {
            foreach ($folder as $item) {
                self::addFolder($array, $item);
            }
        } else {
            if (is_dir($folder) && !in_array($folder, $array)) {
                $array[] = $folder;
            }
        }
    }

    /**
     * TODO
     */
    public function init()
    {
        //        Log::d($this->config);
        Profiler::start('Module Init ' . $this->title, 'modules');
        $this->register_composer();
        if(isset($this->config['app']['controllers'])) {
            Profiler::start('Module Init Controller ' . $this->title, 'controller');
            $controllers = $this->config['app']['controllers'];
            if (!is_array($controllers)) {
                $controllers = array($controllers);
            }
            foreach ($controllers as $controllerFolder) {
                if ($controllerFolder) {
                    $this->load_controllers($this->directory . '/' . $controllerFolder);
                }
            }
            $this->register_autoload_controllers();
            Profiler::stop('Module Init Controller ' . $this->title);
        }
        Profiler::start('Module Init Model ' . $this->title, 'models');
        $this->register_autoload_model();
        Profiler::stop('Module Init Model ' . $this->title);
        $this->register_language();
        Profiler::stop('Module Init ' . $this->title);
        //        $this->register_events();
    }

    public function unload() {
        spl_autoload_unregister($this->spl_autoload_models);
        spl_autoload_unregister($this->spl_autoload_controllers);
        foreach ($this->events as $event) {
            Events::getInstance()->remove($event);
        }
    }

    public function register_language() {
        if(isset($this->config['lang']) && isset($this->config['lang']['resources'])) {
            foreach ($this->config['lang']['resources'] as $item) {
                $path = $this->directory . "/" . $item['path'];
                Bootstrap::getSingleton()->getTranslation()->addResource($path, $item['locale']);
            }
        }
    }

    /**
     * TODO
     */
    public function register_autoload_model()
    {
        if(isset($this->config['app']['model'])) {

            $models = $this->config['app']['model'];
            if (!is_array($models)) {
                $models = array($models);
            }
            $dir = $this->directory;
            foreach ($models as $directory) {
                $files = list_dir($dir . "/" . $directory);
                foreach ($files as $file) {
                    if(strrpos($file, ".php") !== false) {
                        include_once $file;
                    }
                }
            }

//            $this->spl_autoload_models = function ($class) use ($models, $dir) {
//
//                foreach ($models as $directory) {
//
//                    $filename = $dir . '/' . $directory . '/' . $class . '.php';
//                    if (file_exists($filename)) {
//                        include_once $filename;
//                        return true;
//                    }
//                    if(strpos($class, "\\") !== FALSE) {
//                        $name = substr($class, strrpos($class, "\\") + 1);
//                        $filename = $dir . '/' . $directory . '/' . $name . '.php';
//                        if (file_exists($filename)) {
//                            include_once $filename;
//                            return true;
//                        }
//                    }
//                }
//            };
//            spl_autoload_register($this->spl_autoload_models);
        }
    }

    /**
     * TODO
     *
     * @param $root
     * @param null $folder
     */
    public function load_controllers($root, $folder = null)
    {
        $current = $root . ($folder ? '/' . $folder : '');
        if (is_dir($current)) {
            $files = scandir($current);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    $filename = $current . '/' . $file;
                    $name = $folder . '/' . $file;
                    $ext = substr($file, strrpos($file, '.'));
                    if ($ext == '.php') {
                        if(($class = $this->preCache($filename)) === false) {
                            $class = file_get_php_classes($filename);
                            $this->postCache($filename, $class);
                        }
                        if(count($class) != 0) {
                            $this->controllers[$class[0]] = $folder . '/' . $file;
                            $this->controllers_map[$class[0]] = $root . '/' . $folder . '/' . $file;
                        } else {
                            Log::w($filename . " is not a valid controller!");
                        }
                    } elseif (is_dir($filename)) {
                        $this->load_controllers($root, $name);
                    }
                }
            }
        }
    }

    /**
     * TODO
     */
    public function register_autoload_controllers()
    {
        $map = $this->controllers_map;
        $this->spl_autoload_controllers = function ($class) use ($map) {
            if (isset($map[$class])) {
                $file = $map[$class];
                require_once $file;
                return true;
            }
            return false;
        };
        spl_autoload_register($this->spl_autoload_controllers);
    }

    /**
     * TODO
     *
     * @return array
     */
    public function getControllers()
    {
        return $this->controllers;
    }

    /**
     * TODO
     *
     * @return array
     */
    public function getModels($filePath = false)
    {
        $list = array();
        foreach ($this->getModelsFolder() as $model) {
            $folder = $this->directory . "/$model";
            if (is_dir($folder)) {
                $files = scandir($folder);
                foreach ($files as $file) {
                    if (strpos($file, '.php') !== false) {
                        if($filePath) {
                            $list[] = $folder . '/' . $file;
                        } else {
                            $list[] = substr($file, 0, -4);
                        }
                    }
                }
            }
        }
        return $list;
    }

    public function getModelsFolder() {
        $list = array();
        if (!isset($this->config['app']['model'])) {
            return $list;
        }
        $models = $this->config['app']['model'];
        if (empty($models)) {
            return $list;
        }
        if (!is_array($models)) {
            $models = array($models);
        }
        return $models;
    }

    /**
     * Return folder to find views
     *
     * @return array
     */
    public function getViews()
    {
        $config = $this->config;
        $directories = array();
        $dir = $this->directory;
        if (isset($config['app']['views'])) {
            $directoriesTmp = $config['app']['views'];
            if (!is_array($directoriesTmp)) {
                $directoriesTmp = array($directoriesTmp);
            }
            foreach ($directoriesTmp as $directory) {
                $this->addFolder($directories, $dir . '/' . $directory);
            }
        }
        return $directories;
    }

    public function getViewsOverride()
    {
        $config = $this->config;
        $directories = array();
        $dir = $this->directory;
        if (isset($config['app']['override'])) {
            $directoriesTmp = $config['app']['override'];
            if (!is_array($directoriesTmp)) {
                $directoriesTmp = array($directoriesTmp);
            }
            foreach ($directoriesTmp as $directory) {
                $this->addFolder($directories, $dir . '/' . $directory);
            }
        }
        return $directories;
    }

    /**
     * TODO
     *
     * @return array
     */
    public function getTwigExtras()
    {
        if (!isset($this->config['twig']) || empty($this->config['twig'])) {
            return array();
        }
        $array = $this->config['twig'];
        if (!is_array($array)) {
            $array = array($array);
        }
        return $array;
    }

    /**
     * TODO
     *
     * @return mixed
     */
    public function getDirectory()
    {
        return $this->directory;
    }


    /**
     * TODO
     *
     * @param $router
     * @return array
     */
    public function register_router($router)
    {
        $this->router = $router;
        $list = array();
        $controllers = $this->getControllers();
        foreach ($controllers as $controller => $file) {
            $routes = $this->getControllerDefaultRoutes($controller, $file);
            $list[] = $routes;
            foreach ($routes as $route) {
                $this->register_router_controller($router, $route, $controller);
            }
        }
        return $list;
    }

    /**
     * TODO
     *
     * @param $controller
     * @param $file
     * @return array
     */
    public function getControllerDefaultRoutes($controller, $file)
    {
        $array = array();
        if (isset($this->config['app']['routes'])) {
            $routes = $this->config['app']['routes'];
            if (!is_int(key($routes))) {
                $routes = array($routes);
            }
            foreach ($routes as $item) {
                if (isset($item[$controller])) {
                    $array[] = $item[$controller];
                }
            }
        }

        if(isset($this->config['app']['index'])) {
            $index = $this->config['app']['index'];
            if (strpos($file, $index) !== false) {
                $array[] = $this->cleanUrl(substr($file, 0, strpos($file, $index)));
            }
        }
        $array[] = $this->cleanUrl($file);

        return $array;
    }

    /**
     * TODO
     *
     * @param $router \AltoRouter
     * @param $params
     * @param $controller
     */
    public function register_router_controller($router, $params, $controller)
    {
        if (!is_array($params)) {
            $params = array($params);
        }
        $route = $params[0];
        $method = isset($params[1]) ? $params[1] : 'GET|POST';
        $name = isset($params[2]) ? $params[2] : $controller;
        if (in_array($name, $this->controllers_routes)) {
            $name = null;
        } else {
            $this->controllers_routes[] = $name;
        }
        $this->controllers_url_routes[] = $controller . ' ' . $route . ' [' . $method . ']';
        if(!isset(self::$routes[$name])) {
            $router->map($method, $route, array($this, $controller), $name);
            self::$routes[$name] = true;
        } else {
            $router->map($method, $route, array($this, $controller));

        }
    }

    /**
     * TODO
     */
    public function register_events()
    {
        $context = array('module' => $this->title);
        if (isset($this->config['app']['listeners'])) {
            $events = $this->config['app']['listeners'];
            if (!is_array($events)) {
                $events = array($events);
            }
            foreach ($events as $event => $listener) {
                if (!is_array($listener)) {
                    $listener = array($listener);
                }
                foreach ($listener as $fn) {
                    $context['event'] = $event;
                    $context['fn'] = $fn;
                    $event = Events::getInstance()->listen($event, instance_method($fn, $context, array($this)), $this);
                    $event->setModule($this->title);
                    $event->setDefinition($fn);
                    $this->events[] = $event;
                }
            }
        }
    }

    /**
     * Get router
     * @return \AltoRouter
     */
    public function getRouter() {
        return $this->router;
    }

    /**
     * TODO
     *
     * @param $controller
     * @return Controller
     */
    public function instanceController($controller)
    {
        $folder = $this->controllers[$controller];
//        $ref = new \ReflectionClass($controller);
        return new $controller($folder, $this);
    }

    /**
     * TODO
     *
     * @param $controller
     * @param $request Request
     * @return bool|\GLFramework\Response
     * @throws \Exception
     */
    public function run($controller, $request)
    {
        $instance = $controller;
        if (!is_object($controller)) {
            $instance = $this->instanceController($controller);
        }

        if(!($instance instanceof Controller)) {
            throw new \Exception('Controller: ' . $controller . ' is not an instanceOf ' . Controller::class);
        }

        $instance->onCreate();

        if ($instance instanceof Controller) {
            $this->checkUserPermissions($instance);

            return $instance->call($request);
        }
        return false;
    }

    /**
     * @param $instance Controller
     */
    public function checkUserPermissions($instance) {
        if ($instance instanceof Controller\AuthController) {
            if ($instance->user) {
                $result = Events::dispatch('isUserAllowed', array($instance, $instance->user));
                $evt = $result->getEvents();
                foreach ($result->getArray() as $i => $item) {
                    if($item == DISALLOW_USER) {
                        $event = $evt[$i][1];
                        if($event instanceof Event);
                        throw new \Exception('El usuario no tiene permisos para acceder a este sitio. Module: ' . $event->getModule());
                    }
                }
            }
        }
    }


    /**
     * TODO
     *
     * @return mixed
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * TODO
     *
     * @return array
     */
    public function getControllersUrlRoutes()
    {
        return $this->controllers_url_routes;
    }

    /**
     * TODO
     *
     * @param $controller
     * @param $template
     * @param array $args
     * @return string
     */
    public function display($controller, $template, $args = array())
    {
        $view = new View($controller);
        return $view->display($template, $args);
    }

    /**
     * TODO
     *
     * @return bool
     */
    public function isEnabled()
    {
        return ModuleManager::exists($this->title);
    }

    /**
     * TODO
     *
     * @return bool|string
     */
    public function getListName()
    {
        return substr($this->directory, strrpos($this->directory, '/') + 1);
    }

    /**
     * TODO
     *
     * @return string
     */
    public function getFolderContainer()
    {
        if(realpath(dirname($this->directory)) === GL_INTERNAL_MODULES_PATH) return "internal";
        return dirname($this->directory);
    }

    /**
     * TODO
     *
     * @return ModuleSettings[]
     */
    public function getModuleSettings()
    {
        return $this->settings;
    }

    /**
     * TODO
     *
     * @param $url
     * @return bool|string
     */
    private function cleanUrl($url)
    {
        if (strlen($url) > 1 && strrpos($url, '/') === strlen($url) - 1) {
            $url = substr($url, 0, -1);
        }
        if (strrpos($url, '.php') === strlen($url) - 4) {
            $url = substr($url, 0, -4);
        }
        return $url;
    }

    /**
     * TODO
     */
    private function register_composer()
    {
        $composer = $this->getDirectory() . '/vendor/autoload.php';
        if (file_exists($composer)) {
            include_once $composer;
        }
    }
}
