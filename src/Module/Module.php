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
use GLFramework\Events;
use GLFramework\Log;
use GLFramework\Model\UserPage;
use GLFramework\Request;

class Module
{

    var $title;
    var $description;
    private $config;
    private $directory;

    private $controllers = array();
    private $controllers_map = array();
    private $controllers_routes = array();
    private $controllers_url_routes = array();

    /**
     * Module constructor.
     * @param $config
     * @param $directory
     */
    public function __construct($config, $directory)
    {
        $this->config = $config;
        $this->directory = $directory;
        if(isset($this->config['title']))
            $this->title = $this->config['title'];
        if(isset($this->config['description']))
            $this->description = $this->config['description'];
//        $this->config = array_merge_recursive_ex($this->config, Bootstrap::getSingleton()->getConfig());
    }

    public function init()
    {
//        Log::d($this->config);
        $this->register_composer();
        $controllers = $this->config['app']['controllers'];
        if (!is_array($controllers)) $controllers = array($controllers);
        foreach($controllers as $controllerFolder)
        {
            if($controllerFolder)
                $this->load_controllers($this->directory . "/" . $controllerFolder);
        }
        $this->register_autoload_controllers();
        $this->register_autoload_model();
//        $this->register_events();
    }

    public function register_autoload_model()
    {
        $models = $this->config['app']['model'];
        if (!is_array($models)) $models = array($models);
        $dir = $this->directory;

        spl_autoload_register(function ($class) use ($models, $dir) {
            foreach ($models as $directory) {
                $filename = $dir . "/" . $directory . "/$class.php";
                if (file_exists($filename)) {
                    include_once $filename;
                    return true;
                }
            }
        });
    }

    public function load_controllers($root, $folder = null)
    {

        $current = $root . ($folder?"/" . $folder:"");
        if(is_dir($current)) {
            $files = scandir($current);
            foreach($files as $file)
            {
                if($file != "." && $file != "..")
                {
                    $filename = $current . "/" . $file;
                    $name = $folder . "/" . $file;
                    $ext = substr($file, strrpos($file, "."));
                    if($ext == ".php")
                    {
                        $class = file_get_php_classes($filename);
                        $this->controllers[$class[0]] = $folder . "/" . $file;
                        $this->controllers_map[$class[0]] = $root . "/" . $folder . "/" . $file;
                    }
                    else if(is_dir($filename))
                    {
                        $this->load_controllers($root, $name);
                    }
                }
            }
        }

    }

    public function register_autoload_controllers()
    {
        $map = $this->controllers_map;
        spl_autoload_register(function($class) use($map)
        {
           if(isset($map[$class]))
           {
               $file = $map[$class];
               require_once $file;
               return true;
           }
            return false;
        });
    }

    /**
     * @return array
     */
    public function getControllers()
    {
        return $this->controllers;
    }

    public function getModels()
    {
        $list = array();
        $models = $this->config['app']['model'];
        if(empty($models)) return $list;
        if (!is_array($models)) $models = array($models);
        foreach ($models as $model) {
            $folder = $this->directory . "/$model";
            if(is_dir($folder))
            {
                $files = scandir($folder);
                foreach ($files as $file) {
                    if (strpos($file, ".php") !== FALSE) {
                        $list[] = substr($file, 0, -4);
                    }
                }
            }
        }
        return $list;
    }

    /**
     * Return folder to find views
     */
    public function getViews()
    {
        $config = $this->config;
        $directories = array();
        $dir = $this->directory;
        if(isset($config['app']['views']))
        {
            $directoriesTmp = $config['app']['views'];
            if(!is_array($directoriesTmp)) $directoriesTmp = array($directoriesTmp);
            foreach($directoriesTmp as $directory)
            {
                $this->addFolder($directories, $dir . "/" . $directory);
            }
        }
        return $directories;
    }

    public function getTwigExtras()
    {
        if(!isset($this->config['twig']) or empty($this->config['twig'])) return array();
        $array = $this->config['twig'];
        if(!is_array($array)) $array = array($array);
        return $array;
    }

    public static function addFolder(&$array, $folder)
    {
        if(is_array($folder))
        {
            foreach($folder as $item)
            {
                self::addFolder($array, $item);
            }
        }
        else
        {
            if(is_dir($folder) && !in_array($folder, $array)) $array[] = $folder;
        }

    }

    /**
     * @return mixed
     */
    public function getDirectory()
    {
        return $this->directory;
    }


    /**
     * @param $router \AltoRouter
     * @return array
     */
    public function register_router($router)
    {
        $list = array();
        $controllers = $this->getControllers();
        foreach($controllers as $controller => $file)
        {
            $routes = $this->getControllerDefaultRoutes($controller, $file);
            $list[] = $routes;
            foreach($routes as $route)
            {
                $this->register_router_controller($router, $route, $controller);
            }
        }
        return $list;
    }

    public function getControllerDefaultRoutes($controller, $file)
    {
        $array = array();
        if(isset($this->config['app']['routes']))
        {
            $routes = $this->config['app']['routes'];
            if(!is_integer(key($routes))) $routes = array($routes);
            foreach($routes as $item)
            {
                if(isset($item[$controller]))
                {
                    $array[] = $item[$controller];
                }
            }
        }

        $index = $this->config['app']['index'];
        if(strpos($file, $index) !== FALSE)
        {
            $array[] = $this->cleanUrl(substr($file, 0, strpos($file, $index)));
        }
        $array[] = $this->cleanUrl($file);

        return $array;
    }

    private function cleanUrl($url)
    {
        if(strlen($url) > 1 && strrpos($url, "/") == strlen($url) - 1 )
            $url = substr($url, 0, -1);
        if(strrpos($url, ".php") == strlen($url) - 4)
            $url = substr($url, 0, -4);
        return $url;
    }

    /**
     * @param $router \AltoRouter
     * @param $params
     * @param $controller
     */
    public function register_router_controller($router, $params, $controller)
    {
        if(!is_array($params)) $params = array($params);
        $route = $params[0];
        $method = isset($params[1])?$params[1]:"GET|POST";
        $name = isset($params[2])?$params[2]:$controller;
        if(in_array($name, $this->controllers_routes)) $name = null;
        else $this->controllers_routes[] = $name;
        $this->controllers_url_routes[] = $controller . " " . $route . " [" . $method . "]";
        $router->map($method, $route, array($this, $controller), $name);
    }

    private function register_composer()
    {
        $composer = $this->getDirectory() . "/vendor/autoload.php";
        if(file_exists($composer))
        {
            include_once $composer;
        }
    }

    public function register_events()
    {
        $context = array();
        if(isset($this->config['app']['listeners']))
        {
            $events = $this->config['app']['listeners'];
            if(!is_array($events)) $events = array($events);
            foreach($events as $event => $listener)
            {
                if(!is_array($listener)) $listener = array($listener);
                foreach($listener as $fn)
                {
                    Events::getInstance()->listen($event, instance_method($fn, $context, array($this)), $this);
                }
            }
        }
    }

    /**
     * @param $controller
     * @return Controller
     */
    public function instanceController($controller)
    {
        $folder = $this->controllers[$controller];
        return new $controller($folder, $this);
    }
    /**
     * @param $controller
     * @param $request Request
     * @return bool|\GLFramework\Response
     * @throws \Exception
     */
    public function run($controller, $request)
    {
        if(!is_object($controller)) {
            $instance = $this->instanceController($controller);
        }
        else
        {
            $instance = $controller;
        }

        if($instance instanceof Controller)
        {
            if($instance instanceof Controller\AuthController)
            {
                if($instance->user)
                {
                    if(Events::anyFalse(Events::fire('isUserAllowed', array($instance, $instance->user))))
                    {
                        throw new \Exception('El usuario no tiene permisos para acceder a este sitio');
                    }
                }
            }
            $response = $instance->call($request);
            return $response;
        }
        return false;
    }


    /**
     * @return mixed
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return array
     */
    public function getControllersUrlRoutes()
    {
        return $this->controllers_url_routes;
    }
}