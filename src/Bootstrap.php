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
 * Date: 13/1/16
 * Time: 17:34
 */

namespace GLFramework;


use GLFramework\Module\ModuleManager;
use Symfony\Component\Yaml\Yaml;

class Bootstrap
{
    public static $VERSION = "0.2.1";
    private static $singelton;
    /**
     * @var ModuleManager
     */
    private $manager;
    private $events;
    private $config;
    private $directory;
    private $startTime;
    private $init = false;

    /**
     * Bootstrap constructor.
     */
    public function __construct($directory, $config = "config.yml")
    {
        error_reporting(E_ALL);
        $this->startTime = microtime(true);
        $this->events = new Events();
        $this->directory = $directory;
        $this->config = self::loadConfig($this->directory, $config);
        self::$singelton = $this;
    }

    /**
     *
     * Cargar de forma recursiva una configuración
     * @param $folder
     * @param $file
     * @return array|mixed
     */
    public static function loadConfig($folder, $file)
    {
        $filename = $folder . "/{$file}";
        if(!file_exists($filename)) return array();
        $config = Yaml::parse(file_get_contents($filename));
        if(isset($config['include']))
        {
            $value = $config['include'];
            if(!is_array($value)) $value = array($value);
            foreach($value as $subfile)
            {
                $arr = self::loadConfig($folder, $subfile);
                $config = array_merge_recursive_ex($config, $arr);
            }
            unset($config['include']);
        }
        return $config;
    }

    /**
     *
     * Procesa la peticion de respuesta
     * @param $response Response
     */
    public static function dispatch($response)
    {
        $response->display();
    }


    public static function getSingleton()
    {
        return self::$singelton;
    }

    public static function isDebug()
    {
        $config = self::getSingleton()->getConfig();
        return isset($config['app']['debug'])?$config['app']['debug']:false;
    }

    /**
     * Inicia la apliccion de forma estática
     * @param $directory
     * @param string $config
     */
    public static function start($directory, $config = "config.yml")
    {
        try{
                
            define("GL_TESTING", false);
            define("GL_INSTALL", false);
            $bootstrap = new Bootstrap($directory, $config);
            $url = $_SERVER['REQUEST_URI'];
            $method = $_SERVER['REQUEST_METHOD'];
            $bootstrap->run($url, $method)->display();
        } catch (\Exception $ex)
        {
            display_exception($ex);
        }
    }

    /**
     * Inicializar la apliacion de forma interna
     * @throws \Exception
     */
    public function init()
    {
        $this->init = true;
        Log::d("Initializing framework...");
        $this->register_error_handler();
        date_default_timezone_set('Europe/Madrid');

        $this->manager = new ModuleManager($this->config, $this->directory);
        $this->manager->init();
        Log::d("Modules initialized: " . count($this->manager->getModules()));
        Log::d(array_map(function($a){ return $a->title; }, $this->manager->getModules()));
    }

    public static function getAppHash()
    {
        $config = self::getSingleton()->getConfig();
        $string = $config['app']['name'];
        $string .= __FILE__;
        return substr(md5($string), 0, 16);
    }

    /**
     * Prepara e inicia el entorno de testeo
     */
    public function setupTest()
    {
        define("GL_TESTING", true);
        define("GL_INSTALL", false);
//        if(file_exists($this->directory . "/config.dev.yml"))
//        {
//            $this->overrideConfig($this->directory . "/config.dev.yml");
//        }
        $this->init();
    }

    /**
     * Sobreescribe con el archivo de configuración indicado
     * @param $file
     * @throws \Exception
     */
    public function overrideConfig($file)
    {
        if(!$this->init)
        {
            $config = Yaml::parse(file_get_contents($file));
            $this->config = array_merge_recursive_ex($this->config, $config);
        }
        else
        {
            throw new \Exception("Trying to override configuration after init()");
        }
    }

    /**
     * Ejecutar la petición mediante esa url y el método
     * @param null $url
     * @param null $method
     * @return Response
     */
    public function run($url = null, $method = null)
    {
        session_start();
        $this->init();
        Log::i("Welcome to GLFramework");
        Log::i("· Version: " . $this->getVersion());
        Log::i("· PHP Version: " . phpversion());
        Log::i("· Server Type: " . $_SERVER['SERVER_SOFTWARE']);
        Log::i("· Server IP: " . $_SERVER['SERVER_ADDR'] . ":" . $_SERVER['SERVER_PORT']);
        Log::i("· Current User: " . get_current_user());
        Log::i("· Current Folder: " . realpath("."));
        Log::i("· Extensiones de PHP: ");
        Log::i(get_loaded_extensions());
        Events::dispatch('onCoreStartUp', $this->startTime);
        $response = $this->manager->run($url, $method);
        if($response)
            $response->setUri($url);
        return $response;
    }

    /**
     * Instala la base de datos con los modelos actualmente cargados en el init
     * @deprecated Since 0.2.0
     * @throws \Exception
     */
    public function install()
    {
        define("GL_INSTALL", true);
        $this->init();
        echo "<pre>";
        $fail = false;
        $db = new DatabaseManager();
        if ($db->connect()) {
            $this->log("Connection to database ok");

            $this->log("Installing Database...");
            $models = $this->getModels();
            foreach ($models as $model) {
                $instance = new $model(null);
                if ($instance instanceof Model) {
                    $diff = $instance->getStructureDifferences(isset($_GET['drop']));
                    $this->log("Installing table '" . $instance->getTableName() . "' generated by " . get_class($instance) . "...", 2);

                    foreach ($diff as $action) {
                        $this->log("Action: " . $action['sql'] . "...", 3, false);

                        if (isset($_GET['exec']))
                        {
                            try{
                                DBStructure::runAction($db, $instance, $action);
                                $this->log("[OK]", 0);
                            }
                            catch(\Exception $ex)
                            {

                                $this->log("[FAIL]", 0);
                                $fail = true;
                            }
                        }
                        echo "\n";
                    }
                }
            }
            if (!isset($_GET['exec'])) {
                $this->log("Please <a href='?exec'>click here</a> to make this changes in the database.");

            } else {
                $db2 = new DBStructure();
                $db2->setDatabaseUpdate();
                $this->log("All done site ready for develop/production!");
            }
        } else {
            if ($db->getConnection() != null) {
                if (isset($_GET['create_database'])) {
                    if ($db->exec("CREATE DATABASE " . $this->config['database']['database'])) {
                        echo "Database created successful! Please reload the navigator";
                    } else {
                        echo "Can not create the database!";
                    }
                } else {
                    echo "Can not select the database <a href='install.php?create_database'>Try to create database</a>";
                }

            } else {

                echo "Cannot connect to database";
            }
        }
    }

    /**
     * Obtinene la configuración para este contexto
     * @return array|mixed
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Obtiene el directorio en el que se esta ejecutando el framework
     * @return mixed
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * @return ModuleManager
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * Obtiene los modelos que han registrado los módulos.
     * @return array
     */
    public function getModels()
    {
        $list = array();
        foreach($this->getManager()->getModules() as $module)
        {
            foreach($module->getModels() as $model)
            {
                $list[] = $model;
            }
        }
        $files = scandir(__DIR__ . "/Model");
        foreach($files as $file)
        {
            if($file == "." || $file == "..") continue;
            $list[] = "GLFramework\\Model\\" . substr($file, 0, -4);
        }
        return $list;
    }

    public function getTwigExtras()
    {
        $list = array();
        foreach($this->getManager()->getModules() as $module)
        {
            foreach($module->getTwigExtras() as $extra)
            {
                $list[] = $extra;
            }
        }
        return $list;
    }


    /**
     * @param $message
     * @param int $level
     * @param bool $nl
     * @deprecated Since 0.2.0
     */
    public function log($message, $level = 1, $nl = true)
    {
        echo str_repeat("-", $level) . "> " . $message . ($nl?"\n":"");
    }


    function fatal_handler()
    {
        $errfile = "unknown file";
        $errstr = "shutdown";
        $errno = E_CORE_ERROR;
        $errline = 0;

        $error = \error_get_last();
//        error_clear_last();
        if ($error !== NULL) {
            $errno = $error["type"];
            $errfile = $error["file"];
            $errline = $error["line"];
            $errstr = $error["message"];
            if($errno == E_ERROR)
            {
                Log::getInstance()->error($errstr . " " . $errfile . " " . $errline);
                if(isset($this->config['app']['ignore_errors']))
                {
                    if(in_array($errno, $this->config['app']['ignore_errors'])) return;
                }

                if(isset($this->config['app']['debug']) && $this->config['app']['debug'])
                    ($this->format_error($errno, $errstr, $errfile, $errline));
            }
        }

    }

    private function register_error_handler()
    {
        set_error_handler(array($this, "fatal_handler"));
        register_shutdown_function(array($this, "fatal_handler"));
    }

    function format_error($errno, $errstr, $errfile, $errline)
    {

        echo "
  <table>
  <thead><th>Item</th><th>Description</th></thead>
  <tbody>
  <tr>
    <th>Error</th>
    <td><pre>$errstr</pre></td>
  </tr>
  <tr>
    <th>Errno</th>
    <td><pre>$errno</pre></td>
  </tr>
  <tr>
    <th>File</th>
    <td>$errfile</td>
  </tr>
  <tr>
    <th>Line</th>
    <td>$errline</td>
  </tr>
  <tr>
    <th>Trace</th>
    <td><pre>";
        debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        echo "</pre></td>
  </tr>
  </tbody>
  </table>";

//        return $content;
    }

    public function getVersion()
    {
//
        if($package = $this->getComposerInstall())
        {
            if($package->version == "dev-master")
            {
                return "dev-master (" . substr($package->source->reference, 0, 8) . ")";
            }
            return "v" . $package->version;
        }
        return "v" . self::$VERSION;
    }


    public function getComposerInstall()
    {
        if(file_exists("composer.lock"))
        {
            $json = json_decode(file_get_contents("composer.lock"));
            foreach ($json->packages as $package)
            {
                if($package->name == "gestionlan/framework")
                {
                    return $package;
                }
            }
        }

        return false;
    }

    public function toUrl($file)
    {
        $dir = realpath(".");
        $url = str_replace($dir, "", $file);
        $url = (str_replace("//", "/", $url));
        return $url;
    }

    public static function autoDetectConfig($folder = "", $default = "config.yml")
    {
        $host = $_SERVER['HTTP_HOST'];
        if(strpos($host, ":") !== FALSE)
            $host = substr($host, 0, strpos($host, ":"));
        if($host == "localhost") $host = "default";
        if(file_exists("{$folder}{$host}.yml"))
            $config = "{$folder}{$host}.yml";
        else
            $config = $default;
        return $config;
    }

}