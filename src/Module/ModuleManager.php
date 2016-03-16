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
                foreach($this->config['modules'] as $key => $value)
                {
                    $dirbase = $this->directory;
                    if($key == "internal")
                    {
                        $dirbase = __DIR__ . "/../../modules";
                    }
                    if(!is_array($value)) $value = array($value);
                    foreach($value as $name => $extra)
                    {
                        if(is_integer($name)) $name = $extra;
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


    public function run($url = null, $method = null)
    {
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

                return $module->run($controller, $match['params']);
            }
            else
            {
                return $this->mainModule->run(new ErrorController("Controller not found."));
            }
        } catch (\Exception $ex) {
            try {
                return $this->mainModule->run(new ExceptionController($ex));

            } catch (\Exception $ex) {
//                print_debug($ex);
            }
        }
        return false;
    }



}