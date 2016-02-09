<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 13/1/16
 * Time: 17:34
 */

namespace GLFramework;


use GLFramework\Controller\ErrorController;
use GLFramework\Controller\ExceptionController;
use Symfony\Component\Yaml\Yaml;

class Bootstrap
{
    private static $singelton;
    private $config;
    private $directory;
    private $map = array();

    /**
     * Bootstrap constructor.
     */
    public function __construct($directory)
    {
        $this->directory = $directory;
        $this->init();
        self::$singelton = $this;
    }

    public static function getSingleton()
    {
        return self::$singelton;
    }


    public static function start($directory)
    {
        $bootstrap = new Bootstrap($directory);
        $bootstrap->run();
    }

    /**
     * @param $name
     * @return Controller
     */
    public function instanceController($name)
    {
        if(class_exists($name))
        {
            $reflector = new \ReflectionClass($name);
            $fn = $reflector->getFileName();
            $file = str_replace($this->directory . "/", "", $fn);
            $base = substr($file, strpos($file, "/") + 1);
            return new $name($base);
        }
    }
    public function run()
    {
        session_start();

        if(!$this->run_router())
        {
            $filename = $this->getFilename();
            $index = $this->config['app']['index'];
            $files = array("$filename", "$filename/$index", "$filename{$index}");
            try {
                foreach ($files as $file) {
                    $controller = str_replace("/", "_", $file);
                    if(class_exists($controller))
                    {
                        $this->runController($controller);
                        return;
                    }
                }
                $this->runController(new ErrorController("Controller not found: $filename"));
            } catch (\Exception $ex) {
                try {
                    $this->runController(new ExceptionController($ex));

                } catch (\Exception $ex) {
                    print_debug($ex);
                }
            }
        }
    }

    public function run_router()
    {
        $router = new \AltoRouter();
        if(isset($this->config['app']['routes']))
        {
            foreach($this->config['app']['routes'] as $item)
            {
                foreach($item as $controller => $params)
                {
                    if(!is_array($params)) $params = array($params);
                    $route = $params[0];
                    $method = isset($params[1])?$params[1]:"GET";
                    $router->map($method, $route, function() use($controller)
                    {
                        $this->runController($controller, func_get_args());
                    });
                }

            }
            if($match = $router->match())
            {
                $args = array();
                $args[] = $match['params'];
                foreach($match['params'] as $param)
                {
                    $args[] = $param;
                }
                call_user_func_array($match['target'], $args);
                return true;
            }

        }

        return false;


    }

    public function init()
    {
        $this->config = Yaml::parse(file_get_contents($this->directory . "/config.yml"));
        $this->register_autoload_model();
        $this->register_controllers();
        $this->register_error_handler();
    }

    public function register_controllers($folder = null)
    {
        if($folder == null)
        {
            $controllers = $this->config['app']['controllers'];
            if (!is_array($controllers)) $controllers = array($controllers);
            foreach($controllers as $controllerFolder)
            {
                $this->register_controllers($this->directory . "/" . $controllerFolder);
            }
        }
        else{
            if(is_dir($folder)) {
                $files = scandir($folder);
                foreach($files as $file)
                {
                    if($file != "." && $file != "..")
                    {
                        $filename = $folder . "/" . $file;
                        $ext = substr($file, strrpos($file, "."));
                        if($ext == ".php")
                        {
                            include_once $filename;
                        }
                        else if(is_dir($filename))
                        {
                            $this->register_controllers($filename);
                        }
                    }
                }
            }
        }

    }

    public function install()
    {
        echo "<pre>";
        $db = new DBConnection();
        if ($db->connect()) {
            $this->log("Connection to database ok");

            $this->log("Installing Database...");
            $models = $this->getModels();
            foreach ($models as $model) {
                $instance = new $model(null);
                if ($instance instanceof Model) {
                    $diff = $instance->getStructureDifferences();
                    $this->log("Installing table '" . $instance->getTableName() . "' generated by " . get_class($instance) . "...", 2);

                    foreach ($diff as $action) {
                        $this->log("Action: " . $action['sql'] . "...", 3);

                        if (isset($_GET['exec']))
                            $db->exec($action['sql']);
                    }
                }
            }
            if (!isset($_GET['exec'])) {
                $this->log("Please <a href='?exec'>click here</a> to make this changes in the database.");

            } else {

                $this->log("All done site ready for develop/production!");
            }
        } else {
            if ($db->getLink() != null) {
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
     * @param $controller Controller
     * @param array $params
     */
    public function runController($controller, $params = array())
    {
        try
        {
            $class = $this->instanceController($controller);
            $data = call_user_func_array(array($class, "run"), $params);
            echo $class->display($data, $params);
        }
        catch(\Exception $ex)
        {
            $this->runController(new ExceptionController($ex));
        }

    }

    public function getFilename()
    {
        $filename = $_SERVER['REQUEST_URI'];
        $filename = str_replace($this->config['app']['basepath'], "", $filename);
        if (($index = strpos($filename, "?")) !== FALSE) {
            $filename = substr($filename, 0, $index);
        }

        if (strpos($filename, "/") === 0) {
            $filename = substr($filename, 1);
        }
        if (($index = strrpos($filename, ".php")) !== FALSE) {
            $filename = substr($filename, 0, $index);
        }
        return $filename;
    }

    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return mixed
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    public function getModels()
    {
        $list = array();
        $models = $this->config['app']['model'];
        if (!is_array($models)) $models = array($models);
        foreach ($models as $model) {
            $folder = $this->directory . "/$model";
            $files = scandir($folder);
            foreach ($files as $file) {
                if (strpos($file, ".php") !== FALSE) {
                    $list[] = substr($file, 0, -4);
                }
            }
        }
        return $list;
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

    public function log($message, $level = 1)
    {
        echo str_repeat("-", $level) . "> " . $message . "\n";
    }


    function fatal_handler()
    {
        $errfile = "unknown file";
        $errstr = "shutdown";
        $errno = E_CORE_ERROR;
        $errline = 0;

        $error = error_get_last();

        if ($error !== NULL) {
            $errno = $error["type"];
            $errfile = $error["file"];
            $errline = $error["line"];
            $errstr = $error["message"];

            ($this->format_error($errno, $errstr, $errfile, $errline));
        }
    }

    private function register_error_handler()
    {
        register_shutdown_function(array($this, "fatal_handler"));
    }

    function format_error($errno, $errstr, $errfile, $errline)
    {
//        $trace = print_r(debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), true);

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


}