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
use GLFramework\Modules\Debugbar\Debugbar;
use GLFramework\Request;
use GLFramework\Utils\Profiler;

/**
 * Class ModuleManager
 *
 * @package GLFramework\Module
 */
class ModuleManager
{
    private static $instance;
    /**
     * @var Module[]
     */
    private $modules = array();
//    private $modulesDisabled = array();
    private $config;
    private $directory;
    private $router;
    /**
     * @var Module
     */
    private $mainModule;
    private $runningModule;

    /**
     * ModuleManager constructor.
     *
     * @param $config
     * @param $directory
     */
    public function __construct($config, $directory)
    {
        self::$instance = $this;
        $this->config = $config;
        $this->directory = $directory;
        $this->router = $this->newRouter();
        $this->router->map('GET', '/_raw/[*:name]', array($this, 'handleFilesystem'), 'handleFilesystem');
    }

    /**
     * @return \AltoRouter
     */
    private function newRouter()
    {
        $router = new \AltoRouter(array(), $this->getBasePath());
        $router->addMatchTypes(array(
            'idd' => '([0-9]+|add)?',
        ));
        return $router;
    }

    /**
     * Obtiene la instancia a Gestor de Módulos
     *
     * @return ModuleManager
     */
    public static function getInstance()
    {
        return self::$instance;
    }


    public function getBasePath()
    {
        return isset($this->config['app']) && isset($this->config['app']['basepath']) ?
            $this->config['app']['basepath'] : "";
    }

    /**
     * Obtener un módulo por su nombre
     *
     * @param $name
     * @return Module
     */
    public static function getModuleInstanceByName($name)
    {
        $instance = self::getInstance();
        foreach ($instance->getModules() as $module) {
            if ($module->title === $name) {
                return $module;
            }
        }
        return null;
    }

    /**
     * Si el modulo existe, entonces devuelve true, en cualquier otro caso false.
     *
     * @param $string string Titulo del modulo
     * @return bool
     */
    public static function exists($string)
    {
        $instance = self::getInstance();
        foreach ($instance->getModules() as $module) {
            if ($module->title === $string) {
                return true;
            }
        }
        return false;
    }

    /**
     * Comprueba que exista el controlador
     *
     * @param $key
     * @return bool
     */
    public static function existsController($key)
    {
        $instance = self::getInstance();
        foreach ($instance->getModules() as $module) {
            foreach ($module->getControllers() as $controller => $file) {
                if ($key == $controller) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Instancia un nuevo controlador
     *
     * @param $key
     * @return bool|\GLFramework\Controller
     */
    public static function instanceController($key)
    {
        $instance = self::getInstance();
        foreach ($instance->getModules() as $module) {
            foreach ($module->getControllers() as $controller => $file) {
                if ($key == $controller) {
                    return $module->instanceController($controller);
                }
            }
        }
        return false;
    }

    /**
     * Para un controlador dado, devuelve su módulo
     *
     * @param $key
     * @return bool|Module
     */
    public static function getModuleForController($key)
    {
        $instance = self::getInstance();
        foreach ($instance->getModules() as $module) {
            foreach ($module->getControllers() as $controller => $file) {
                if ($key == $controller) {
                    return $module;
                }
            }
        }
        return false;
    }

    /**
     * Obtener el enrutador
     *
     * @return \AltoRouter
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * Lista los módulos presentes
     *
     * @return Module[]
     */
    public function getModules()
    {
        return $this->modules;
    }

    /**
     * Obtener el módulo principal
     *
     * @return Module
     */
    public function getMainModule()
    {
        return $this->mainModule;
    }

    /**
     * Dado un módulo, obtiene las vistas asociadas, si no hay módulo se listan todas las vistas
     *
     * @param bool|Module $module
     * @return array
     */
    public function getViews($module = false)
    {
        $views = array();
        foreach ($this->getModules() as $module2) {
            foreach ($module2->getViewsOverride() as $view) {
                $views[] = $view;
            }
        }
        if ($module) {
            foreach ($module->getViews() as $view) {
                $views[] = $view;
            }
        }
        foreach ($this->getModules() as $module2) {
            if ($module2 !== $module) {
                foreach ($module2->getViews() as $view) {
                    $views[] = $view;
                }
            }
        }
        $mainModule = ModuleManager::getInstance()->getMainModule();
        if ($module != $mainModule) {
            Module::addFolder($views, $mainModule->getViews());
        }
        // Add framework views
        Module::addFolder($views, realpath(__DIR__ . '/../../..') . '/');
        Module::addFolder($views, realpath(__DIR__ . '/../..') . '/');
        Module::addFolder($views, realpath(__DIR__ . '/../..') . '/views');
        Module::addFolder($views, realpath(__DIR__ . '/../..') . '/modules');
        return $views;
    }

    /**
     * Inicializar el gestor de módulos
     *
     * @throws \Exception
     */
    public function init()
    {
        Profiler::start('moduleManager');
        $module = $this->load($this->directory, array(), true);
        if ($module) {
            $this->mainModule = $module;
            $this->add($module);
            Profiler::start('moduleManager Init');
            $this->mainModule->init();
            $this->mainModule->register_events();
            if (isset($this->config['modules'])) {
                $this->loadConfig($this->config);
            }

            foreach ($this->modules as $module) {
                    $module->init();
                    $module->register_router($this->router);
            }
            foreach ($this->modules as $module) {
                if ($module !== $this->mainModule) {
                    $module->register_events();
                }
            }
            Profiler::stop('moduleManager Init');
        } else {
            $file = $this->directory . "/config.yml";
            throw new \Exception('Can\'t not load the main module! Looking for: \'' . $file . '\'. folder 
            realpath: ' . realpath($this->directory));
        }
        Profiler::stop('moduleManager');
    }

    public function checkModulesPolicy()
    {
        $remove = array();
        foreach ($this->modules as $module) {
            if (Events::dispatch('isModuleEnabled', array($module))->anyFalse()) {
                $remove[] = $module;
            }
        }
        foreach ($remove as $module) {
            $this->remove($module);
        }
    }

    /**
     * Cargar la configuracion de aplicacion.
     * Aqui se inicializa los módulos y sus dependencias.
     *
     * @param $config
     * @throws \Exception
     */
    public function loadConfig($config)
    {
        $modules = $config['modules'];
        if (!is_array($modules)) {
            $modules = array($modules);
        }
        // Los modulos se definen de la siguiente manera:
        // modules:
        //      [path_to_module]:
        //          [module_folder]: { [extra_config] }

        // Pre-load modules:
        $resolv = array();
        foreach ($modules as $folder => $list) {
            // Resolver el tipo de directorio
            if ((string)$folder === 'internal') { // Para especificar un modulo interno
                $folder = __DIR__ . '/../../modules';
            } elseif (is_numeric($folder)) { // Para especificar un modulo simple
                $folder = $this->directory . 'modules';
            } else {
                $folder = $this->directory . fix_folder($folder);
            }
            if (!is_array($list)) {
                $list = array($list);
            }

            foreach ($list as $name => $moduleConfig) {
                if (is_int($name) && empty($moduleConfig)) { // Tipo: [0] => ''
                    continue;
                }
                if (!is_string($name) && is_array($moduleConfig)) {
                    $name = key($moduleConfig);
                    $moduleConfig = current($moduleConfig);
                } elseif (is_int($name)) {
                    // tipo: "- admin" o "admin"
                    $name = $moduleConfig;
                    $moduleConfig = array();
                }
                $path = $folder . '/' . $name;
                if (!isset($resolv[$path])) {
                    $resolv[$path] = array('name' => $name, 'config' => array());
                }
//                $resolv[$name][] = $moduleConfig;
                $resolv[$path]['config'] = array_merge_recursive_ex($resolv[$path]['config'], $moduleConfig);
            }
        }
        foreach ($resolv as $path => $info) {
            $moduleConfig = $info['config'];
            $name = $info['name'];
            $module = $this->load($path, $moduleConfig);
            if ($module) {
                if (!$this->exists($module->title)) {
                    $this->add($module);
                    $this->loadModuleDependencies($module);
                }
            } else {
                throw new \Exception('Can\'t not load module: ' . $name . ' in directory: \'' .
                    $path . '\'');
            }
        }
    }

    /**
     * Cargar un módulo para un directorio
     *
     * @param $folder
     * @param null $extra
     * @param bool $main
     * @return Module|null
     */
    public function load($folder, $extra = null, $main = false)
    {
        Profiler::start('load ' . $folder);
        $configFile = $folder . '/config.yml';
        if (file_exists($configFile)) {
            $config = $this->loadAndCache($folder, 'config.yml');
        } else {
            if (!$main) {
                return null;
            }
            $config = Bootstrap::getSingleton()->getConfig();
            if (!$config) {
                return null;
            }
        }
        if (is_array($extra)) {
            $config = array_merge_recursive_ex($config, $extra);
        }
        $module = new Module($config, $folder);
        Profiler::stop('load ' . $folder);

        return $module;
    }

    private function loadAndCache($folder, $file) {

        $config = Bootstrap::loadConfig($folder, 'config.yml');

        return $config;
    }

    /**
     * Cargar dependencias del módulo
     *
     * @param $module Module
     */
    public function loadModuleDependencies($module)
    {
        $config = $module->getConfig();
        if (isset($config['modules'])) {
            $this->loadConfig($config);
        }
    }

    /**
     * Añadir instancia de módulo a la lista
     *
     * @param $module Module
     */
    public function add($module)
    {
        if ($module !== null) {
            $this->modules[] = $module;
        }
    }

    public function remove($module)
    {
        if ($module !== null) {
            $i = array_search($module, $this->modules);
            if ($i >= 0) {
                $this->modules[$i]->unload();
                unset($this->modules[$i]);
            }
        }
    }

    /**
     * Devuelve true si el módulo está habilitado
     *
     * @param $name
     * @return bool
     */
    public function isEnabled($name)
    {
        foreach ($this->modules as $module) {
            if ($name === $module->title) {
                return true;
            }
        }
        return false;
    }


    /**
     * Ejecutar la ruta
     *
     * @param null $url
     * @param null $method
     * @return \GLFramework\Response|bool
     */
    public function run($url = null, $method = null)
    {
        $request = new Request($method, $url);
        try {
//            foreach ($this->modules as $module) {
//
//            }
            if ($match = $this->router->match($url, $method)) {
                if ($match['name'] === 'handleFilesystem') {
                    return $this->handleFilesystem($match['params'], $request);
                } else {
                    $target = $match['target'];
                    $module = $target[0];
                    if ($module instanceof Module) {
                        if ($this->isEnabled($module->title)) {
                            $this->runningModule = $module;
                            $controller = $target[1];
                            $request->setParams($match['params']);
                            return $module->run($controller, $request);
                        } else {
                            return $this->mainModule->run(new ErrorController("This module is disabled by your 
                            policy"),
                                $request);
                        }
                    }
                }
            }


            return $this->mainModule->run(new ErrorController("Controller for '$url' not found. " .
                $this->getRoutes()),
                $request);
        } catch (\Exception $ex) {
            Events::dispatch('onException', $ex);
            return $this->mainModule->run(new ExceptionController($ex), $request);
        } catch (\Throwable $ex) {
            Events::dispatch('onError', $ex);
            return $this->mainModule->run(new ExceptionController($ex), $request);
        }
    }

    /**
     * Listado de rutas
     *
     * @return string
     */
    public function getRoutes()
    {
        if ($this->config['app']['debug']) {
            $html = '<pre>';
            $result = array();
            foreach ($this->modules as $module) {
                $list = $module->getControllersUrlRoutes();
                $result = array_merge($list, $result);
            }
            $html .= implode("\n", $result);
            $html .= '</pre>';
            return $html;
        }
        return "";
    }

    /**
     * Llamada a los archivos del sistema
     *
     * @param $args
     * @param $request
     * @return bool|\GLFramework\Response
     */
    public function handleFilesystem($args, $request)
    {
        $file = urldecode($args['name']);
        $filesystem = new Filesystem($file);
        if ($filesystem->exists()) {
            if ($filesystem->isPublic()) {
                header('Content-Type: ' . mime_content_type($filesystem->getAbsolutePath()));
                $filesystem->read(true);
                die();
            } else {
                return $this->mainModule->run(new ErrorController('Este archivo no es descargable'), $request);
            }
        } else {
            return $this->mainModule->run(new ErrorController('No se ha encontrado este archivo!'), $request);
        }
    }

    /**
     * Obtiene el módulo en ejecución.
     *
     * @return mixed
     */
    public function getRunningModule()
    {
        return $this->runningModule;
    }
}
