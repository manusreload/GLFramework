<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 12/02/16
 * Time: 11:58
 */

namespace GLFramework\Module;


use GLFramework\Controller;

class Module
{

    private $config;
    private $directory;

    private $controllers = array();
    private $controllers_map = array();
    private $controllers_routes = array();

    /**
     * Module constructor.
     * @param $config
     * @param $directory
     */
    public function __construct($config, $directory)
    {
        $this->config = $config;
        $this->directory = $directory;
    }


    public function init()
    {
        $controllers = $this->config['app']['controllers'];
        if (!is_array($controllers)) $controllers = array($controllers);
        foreach($controllers as $controllerFolder)
        {
            $this->load_controllers($this->directory . "/" . $controllerFolder);
        }
        $this->register_autoload_controllers();
        $this->register_autoload_model();
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


    /**
     * @param $router \AltoRouter
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
    }

    public function getControllerDefaultRoutes($controller, $file)
    {
        $array = array();

        if(isset($this->config['app']['routes']))
        {
            $routes = $this->config['app']['routes'];
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
        $router->map($method, $route, array($this, $controller), $name);
    }

    public function run($controller, $params = array())
    {
        if(!is_object($controller)) {
            $folder = $this->controllers[$controller];
            $instance = new $controller($folder);
        }
        else
        {
            $instance = $controller;
        }

        if($instance instanceof Controller)
        {
            $data = call_user_func_array(array($instance, "run"), $params);
            echo $instance->display($data, $params);
            return true;
        }
        return false;
    }


}