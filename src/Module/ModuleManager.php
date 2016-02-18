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

    /**
     * ModuleManager constructor.
     * @param $config
     * @param $directory
     */
    public function __construct($config, $directory)
    {
        $this->config = $config;
        $this->directory = $directory;
        $this->router = new \AltoRouter(array(), $this->config['app']['basepath']);
    }

    /**
     * @return \AltoRouter
     */
    public function getRouter()
    {
        return $this->router;
    }



    public function init()
    {
        $module = $this->load($this->directory);
        if($module)
        {
            $this->mainModule = $module;
            $this->add($module);
            $modulesDirectory = ($this->directory . "/modules");
            if(is_dir($modulesDirectory))
            {
                $modules = scandir($modulesDirectory);
                foreach($modules as $folder)
                {
                    if($folder != "." && $folder != "..")
                    {
                        $module = $this->load($modulesDirectory . "/" . $folder);
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

    public function load($folder)
    {
        $configFile = $folder . "/config.yml";
        if(file_exists($configFile))
        {
            $config = Yaml::parse(file_get_contents($configFile));
            $config = array_merge($config, $this->config);

            return new Module($config, $folder);
        }
        return null;
    }

    public function add($module)
    {
        if($module != null)
            $this->modules[] = $module;

    }

    public function run()
    {
        foreach($this->modules as $module)
        {
            $module->register_router($this->router);
        }
        try {
            if($match = $this->router->match())
            {
                $target = $match['target'];
                $module = $target[0];
                $controller = $target[1];

                return $module->run($controller, $match['params']);
            }
            else
            {
                $this->mainModule->run(new ErrorController("Controller not found."));
            }
        } catch (\Exception $ex) {
            try {
                $this->mainModule->run(new ExceptionController($ex));

            } catch (\Exception $ex) {
                print_debug($ex);
            }
        }
        return false;
    }
}