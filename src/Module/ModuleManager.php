<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 12/02/16
 * Time: 11:58
 */

namespace GLFramework\Module;


use GLFramework\Controller\ErrorController;
use GLFramework\Controller\ExceptionController;
use GLFramework\Events;
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
    }

    public static function getInstance()
    {
        return self::$instance;
    }

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
    public function getViews($module)
    {
        $views = $module->getViews();
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
        return $views;
    }

    public function init()
    {
        $time = microtime();
        $module = $this->load($this->directory);
        if($module)
        {
            $this->mainModule = $module;
            $this->add($module);
            if(isset($this->config['modules']))
            {
                $modules = $this->config['modules'];
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
                    if(!is_array($value)) $value = array($value);
                    foreach($value as $name => $extra)
                    {
                        if(is_integer($name)) $name = $extra;
                        if(is_array($extra))
                        {
                            $name = key($extra);
                            $extra = current($extra);
                        }
                        $module = $this->load($dirbase . "/" . $name, $extra);
                        if($module)
                            $this->add($module);

                    }
                }
            }

            foreach($this->modules as $module)
            {
                $module->init();
            }
        }
        else
        {
            throw new \Exception("Can't not load the main module!");
        }
    }

    public function load($folder, $extra = null)
    {
        $configFile = $folder . "/config.yml";
        if(file_exists($configFile))
        {
            $config = Yaml::parse(file_get_contents($configFile));
            $config = array_merge($this->config, $config);
            if(is_array($extra))
            {
                $config = array_merge_recursive_ex($config, $extra);
            }
            return new Module($config, $folder);
        }
        return null;
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
        foreach($this->modules as $module)
        {
            $module->register_router($this->router);
        }
        try {

            if($match = $this->router->match($url, $method))
            {
                $target = $match['target'];
                $module = $target[0];
                $controller = $target[1];
                $request->setParams($match['params']);
                return $module->run($controller, $request);
            }
            else
            {
                return $this->mainModule->run(new ErrorController("Controller not found. " . $this->getRoutes()), $request);
            }
        } catch (\Exception $ex) {
            return $this->mainModule->run(new ExceptionController($ex), $request);
        } catch (\Throwable $ex) {
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



}