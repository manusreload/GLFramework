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

use GLFramework\Globals\Server;
use GLFramework\Module\ModuleManager;

define("GL_INTERNAL_MODULES_PATH", realpath(__DIR__ . "/../modules"));
/**
 * Class Bootstrap
 *
 * @package GLFramework
 */
class Bootstrap
{
    public static $VERSION = '0.2.1';
    private static $singelton;
    /**
     * @var ModuleManager
     */
    private $manager;
    private $events;
    private $config;
    private $directory;
    private $startTime;
    private $initTime;
    private $init = false;
    private $inited = false;
    private $configFile;
    private $translation;

    private $requireExtensions = array('ctype', 'json', 'hash', 'curl', 'pdo', 'pdo_mysql', 'iconv', 'zip', 'filter');
    private $requireExtensionsTest = array('mbstring');

    /**
     * Bootstrap constructor.
     *
     * @param $directory
     * @param string $config
     */
    public function __construct($directory, $config = 'config.yml')
    {
        error_reporting(E_ALL);
        $this->startTime = microtime(true);
        $this->events = new Events();
        $this->directory = $directory;
        $this->configFile = $config;
        $this->config = self::loadConfig($this->directory, $config);
        self::$singelton = $this;
    }

    /**
     *
     * Cargar de forma recursiva una configuración
     *
     * @param $folder
     * @param $file
     * @return array|mixed
     */
    public static function loadConfig($folder, $file)
    {
        $filename = $folder . "/{$file}";
        if (!file_exists($filename)) {
            return array();
        }
        $config = Yaml::parse($filename);
        if (isset($config['include'])) {
            $value = $config['include'];
            if (!is_array($value)) {
                $value = array($value);
            }
            foreach ($value as $subfile) {
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
     *
     * @param $response Response
     */
    public static function dispatch($response)
    {
        $response->display();
    }

    /**
     * TODO
     *
     * @return Bootstrap
     */
    public static function getSingleton()
    {
        return self::$singelton;
    }

    /**
     * TODO
     *
     * @return bool
     */
    public static function isDebug()
    {
        $config = self::getSingleton()->getConfig();
        return isset($config['app']['debug']) ? $config['app']['debug'] : false;
    }

    /**
     * Inicia la apliccion de forma estática
     *
     * @param $directory
     * @param string $config
     */
    public static function start($directory, $config = 'config.yml')
    {
        try {
            define('GL_TESTING', false);
            define('GL_INSTALL', false);
            $bootstrap = new Bootstrap($directory, $config);
            $url = Server::get('REQUEST_URI');
            $method = Server::get('REQUEST_METHOD');
            $bootstrap->run($url, $method)->display();
        } catch (\Exception $ex) {
            display_exception($ex);
        }
    }

    /**
     * Simple script router
     *
     * @param $directory
     * @param string $config
     * @return bool
     */
    public static function router($directory, $config = 'config.yml')
    {
        $request = Server::get('PHP_SELF');
        $root = Server::get('DOCUMENT_ROOT');
        $file = $root . $request;
        if(file_exists($file) && !is_dir($file))
        {
            return false;
        }
        else
        {
            Bootstrap::start($directory, $config);
            return true;
        }

    }


    /**
     * TODO
     *
     * @return bool|string
     */
    public static function getAppHash()
    {
        $bs = self::getSingleton();
        $config = $bs->getConfig();
        $string = $bs->getAppName();
        $string .= $bs->getConfigFile();
        $string .= $bs->getDirectory();
        $string .= __FILE__;
        return substr(md5($string), 0, 16);
    }

    public function getAppName() {
        return isset( $config['app'] ) && isset( $config['app']['name'] ) ? $config['app']['name'] : "";
    }

    /**
     * TODO
     *
     * @param string $folder
     * @param string $default
     * @return string
     */
    public static function autoDetectConfig($folder = '', $default = 'config.yml')
    {
        $host = Server::get('HTTP_HOST');
        if (strpos($host, ':') !== false) {
            $host = substr($host, 0, strpos($host, ':'));
        }
        if ($host === 'localhost' or filter_var($host, FILTER_VALIDATE_IP)) {
            $host = 'default';
        }
        if (file_exists($folder . $host . '.yml')) {
            $config = $folder . $host . '.yml';
        } else {
            $config = $default;
        }
        return $config;
    }

    /**
     * Inicializar la apliacion de forma interna
     *
     * @throws \Exception
     */
    public function init()
    {
        $this->initTime = microtime(true);
        $this->init = true;
        Log::d('Initializing framework...');
//        $this->register_error_handler();
        date_default_timezone_set('Europe/Madrid');
        $this->setupLanguage();

        $this->manager = new ModuleManager($this->config, $this->directory);
        $this->manager->init();
        Log::d('Module manager initialized');
        $this->inited = true;

//        Log::d('Modules initialized: ' . count($this->manager->getModules()));
//        Log::d(array_map(function ($a) {
//            return $a->title;
//        }, $this->manager->getModules()));
    }

    /**
     * Prepara e inicia el entorno de testeo
     */
    public function setupTest()
    {
        define('GL_TESTING', true);
        define('GL_INSTALL', false);
        if(!isset($_SESSION)) {
            global $_SESSION;
            $_SESSION = array();
        }
        //        if(file_exists($this->directory . '/config.dev.yml'))
        //        {
        //            $this->overrideConfig($this->directory . '/config.dev.yml');
        //        }
        $this->init();
    }

    /**
     * Sobreescribe con el archivo de configuración indicado
     *
     * @param $file
     * @throws \Exception
     */
    public function overrideConfig($file)
    {
        if (!$this->init) {
            $config = Yaml::parse($file);
            $this->config = array_merge_recursive_ex($this->config, $config);
        } else {
            throw new \Exception('Trying to override configuration after init()');
        }
    }

    /**
     * TODO
     */
    public function startSession()
    {
        if (isset($this->config['app']['tempdir'])) {
            $base = sys_get_temp_dir();
            $folder = $base . '/' . $this->config['app']['tempdir'];
            if (!is_dir($folder) && is_executable($base)) {
                mkdir($folder);
            }
            session_save_path(sys_get_temp_dir() . '/glframework_session');
        }
        @session_start();
    }

    /**
     * Ejecutar la petición mediante esa url y el método
     *
     * @param null $url
     * @param null $method
     * @return Response
     */
    public function run($url = null, $method = null)
    {
        $this->startSession();
        if(!$this->inited)
            $this->init();
        Log::i('Welcome to GLFramework');
        Log::i('· Version: ' . $this->getVersion());
        Log::i('· PHP Version: ' . PHP_VERSION);
        Log::i('· Server Type: ' . Server::get('SERVER_SOFTWARE', 'unknown'));
        Log::i('· Server IP: ' . Server::get('SERVER_ADDR', '127.0.0.1') . ':' . (Server::get('SERVER_PORT', '0')));
        Log::i('· Current User: ' . get_current_user());
        Log::i('· Current Folder: ' . realpath('.'));
        Log::i('· Extensiones de PHP: ');
        Log::i(get_loaded_extensions());
        Log::i('· Modules priority: ');
        Log::i(array_map(function($module) { return $module->title; }, $this->manager->getModules()));
        Events::dispatch('onCoreStartUp', array($this->startTime, $this->initTime));
        $this->manager->checkModulesPolicy();
        $this->setupDatabase();
        $response = $this->manager->run($url, $method);
        Log::i('Sending response...');
        if ($response) {
            $response->setUri($url);
        }
        return $response;
    }

    private function setupDatabase() {
        $db = new DatabaseManager();
        if ($db->connect()) {
            $db->checkDatabaseStructure();
        }
    }
    private function setupLanguage() {
        $this->translation = new Translation();
    }

    /**
     * Instala la base de datos con los modelos actualmente cargados en el init
     *
     * @deprecated Since 0.2.0
     * @throws \Exception
     */
    public function install()
    {
        define('GL_INSTALL', true);
        $this->init();
        echo '<pre>';
        $fail = false;
        $db = new DatabaseManager();
        if ($db->connect()) {
            $this->log('Connection to database ok');

            $this->log('Installing Database...');
            $models = $this->getModels();
            foreach ($models as $model) {
                $instance = new $model(null);
                if ($instance instanceof Model) {
                    $diff = $instance->getStructureDifferences($db, isset($_GET['drop']));
                    $this->log('Installing table \'' . $instance->getTableName() . '\' generated by ' . get_class($instance) . '...',
                        2);
                    foreach ($diff as $action) {
                        $this->log('Action: ' . $action['sql'] . '...', 3, false);

                        if (isset($_GET['exec'])) {
                            try {
                                DBStructure::runAction($db, $instance, $action);
                                $this->log('[OK]', 0);
                            } catch (\Exception $ex) {
                                $this->log('[FAIL]', 0);
                                $fail = true;
                            }
                        }
                        echo "\n";
                    }
                }
            }
            if (!isset($_GET['exec'])) {
                $this->log('Please <a href="?exec">click here</a> to make this changes in the database.');
            } else {
                $db2 = new DBStructure();
                $db2->setDatabaseUpdate();
                $this->log('All done site ready for develop/production!');
            }
        } else {
            if ($db->getConnection() !== null) {
                if (isset($_GET['create_database'])) {
                    if ($db->exec('CREATE DATABASE ' . $this->config['database']['database'])) {
                        echo 'Database created successful! Please reload the navigator';
                    } else {
                        echo 'Can not create the database!';
                    }
                } else {
                    echo 'Can\'t select the database <a href="install.php?create_database">Try to create database</a>';
                }
            } else {
                echo 'Cannot connect to database';
            }
        }
    }

    /**
     * Obtinene la configuración para este contexto
     *
     * @return array|mixed
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Obtiene el directorio en el que se esta ejecutando el framework
     *
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
     *
     * @return array
     */
    public function getModels()
    {
        $list = array();
        foreach ($this->getManager()->getModules() as $module) {
            foreach ($module->getModels() as $model) {
                $list[] = $model;
            }
        }
        $files = scandir(__DIR__ . '/Model');
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $list[] = 'GLFramework\\Model\\' . substr($file, 0, -4);
        }
        return $list;
    }

    public function getModelsFolder() {
        $list = array();
        foreach ($this->getManager()->getModules() as $module) {
            foreach ($module->getModelsFolder() as $model) {
                $list[] = $model;
            }
        }
        return $list;
    }

    /**
     * TODO
     *
     * @return array
     */
    public function getTwigExtras()
    {
        $list = array();
        foreach ($this->getManager()->getModules() as $module) {
            foreach ($module->getTwigExtras() as $extra) {
                $list[] = $extra;
            }
        }
        return $list;
    }

    /**
     * TODO
     *
     * @param $message
     * @param int $level
     * @param bool $nl
     * @deprecated Since 0.2.0
     */
    public function log($message, $level = 1, $nl = true)
    {
        echo str_repeat('-', $level) . '> ' . $message . ($nl ? "\n" : '');
    }

    /**
     * TODO
     */
    function fatal_handler()
    {
        $errfile = 'unknown file';
        $errstr = 'shutdown';
        $errno = E_CORE_ERROR;
        $errline = 0;

        $error = \error_get_last();
        //        error_clear_last();
        if ($error !== null) {
            $errno = $error['type'];
            $errfile = $error['file'];
            $errline = $error['line'];
            $errstr = $error['message'];
            if ($errno === E_ERROR) {
                Log::getInstance()->error($errstr . " " . $errfile . " " . $errline);
                if (isset($this->config['app']['ignore_errors'])) {
                    if (in_array($errno, $this->config['app']['ignore_errors'])) {
                        return;
                    }
                }

                if (isset($this->config['app']['debug']) && $this->config['app']['debug']) {
                    ($this->format_error($errno, $errstr, $errfile, $errline));
                }
            }
        }
    }

    /**
     * TODO
     *
     * @param $errno
     * @param $errstr
     * @param $errfile
     * @param $errline
     */
    function format_error($errno, $errstr, $errfile, $errline)
    {

        echo '
  <table>
  <thead><th>Item</th><th>Description</th></thead>
  <tbody>
  <tr>
    <th>Error</th>
    <td><pre>' . $errstr . '</pre></td>
  </tr>
  <tr>
    <th>Errno</th>
    <td><pre>' . $errno . '</pre></td>
  </tr>
  <tr>
    <th>File</th>
    <td>' . $errfile . '</td>
  </tr>
  <tr>
    <th>Line</th>
    <td>' . $errline . '</td>
  </tr>
  <tr>
    <th>Trace</th>
    <td><pre>';
        debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        echo '</pre></td>
  </tr>
  </tbody>
  </table>';

        //        return $content;
    }

    /**
     * TODO
     *
     * @return string
     */
    public function getVersion()
    {
        if ($package = $this->getComposerInstall()) {
            if ($package->version === 'dev-master') {
                return 'dev-master (' . substr($package->source->reference, 0, 8) . ')';
            }
            return 'v' . $package->version;
        }
        return 'v' . self::$VERSION;
    }

    /**
     * TODO
     *
     * @return bool
     */
    public function getComposerInstall()
    {
        if (file_exists('composer.lock')) {
            $json = json_decode(file_get_contents('composer.lock'));
            foreach ($json->packages as $package) {
                if ($package->name === 'gestionlan/framework') {
                    return $package;
                }
            }
        }

        return false;
    }

    /**
     * TODO
     *
     * @param $file
     * @return mixed
     */
    public function toUrl($file)
    {
        $dir = realpath('.');
        $url = str_replace($dir, '', $file);
        $url = str_replace('//', '/', $url);
        return $url;
    }

    /**
     * TODO
     */
    private function register_error_handler()
    {
        set_error_handler(array($this, 'fatal_handler'));
        register_shutdown_function(array($this, 'fatal_handler'));
    }

    public function relative($path)
    {
        $a = realpath($this->getDirectory());
        $b = realpath($path);
        return str_replace($a . "/", $b, "");
    }

    /**
     * @return mixed
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * @return string
     */
    public function getConfigFile()
    {
        return $this->configFile;
    }

    public function getCurrentPath()
    {
        return realpath(".");
    }

    /**
     * @return bool
     */
    public function isInit()
    {
        return $this->init;
    }

    /**
     * @return bool
     */
    public function isInited()
    {
        return $this->inited;
    }

    /**
     * @return Translation
     */
    public function getTranslation()
    {
        return $this->translation;
    }





}
