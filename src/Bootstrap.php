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

    /**
     * Bootstrap constructor.
     */
    public function __construct($directory)
    {
        $this->directory = $directory;
        $this->config = Yaml::parse(file_get_contents($this->directory . "/config.yml"));
        $this->register_autoload_model();
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

    public function run()
    {
        session_start();
        $filename = $this->getFilename();
        $index = $this->config['app']['index'];
        $controllers = $this->config['app']['controllers'];
        if (!is_array($controllers)) $controllers = array($controllers);
        $files = array("$filename", "$filename/$index");
        try {
            foreach ($files as $file) {
                foreach ($controllers as $controllerFolder) {

                    $path = $this->directory . "/$controllerFolder/" . $file . ".php";
                    if (file_exists($path)) {
                        $controller = str_replace("/", "_", $file);
                        if (strpos($controller, "_") === 0) {
                            $controller = substr($controller, 1);
                        }
                        include_once $path;
                        $class = new $controller();
                        $class->setTemplate($file . ".twig");
                        $this->runController($class);
                        return;
                    }
                }
            }
            $this->runController(new ErrorController("Controller not found: $filename"));

        } catch (\Exception $ex) {
            $this->runController(new ExceptionController($ex));
        }

    }

    public function install()
    {
        echo "<pre>";
        $db = new DBConnection();
        if($db->connect())
        {
            $this->log("Connection to database ok");

            $this->log("Installing Database...");
            $models = $this->getModels();
            foreach($models as $model)
            {
                $instance = new $model(null);
                if($instance instanceof Model)
                {
                    $diff = $instance->getStructureDifferences();
                    $this->log("Installing table '" . $instance->getTableName() . "'...", 2);

                    foreach($diff as $action)
                    {
                        $this->log("Action: " . $action['sql'] . "...", 3);
                        $db->exec($action['sql']);
                    }
                }
            }

            $this->log("All done site ready for develop/production!");
        }
        else{
            if($db->getLink() != null)
            {
                if(isset($_GET['create_database']))
                {
                    if($db->exec("CREATE DATABASE " . $this->config['database']['database']))
                    {
                        echo "Database created successful! Please reload the navigator";
                    }
                    else{
                        echo "Can not create the database!";
                    }
                }
                else{
                    echo "Can not select the database <a href='install.php?create_database'>Try to create database</a>";
                }

            }
            else{

                echo "Cannot connect to database";
            }
        }
    }

    /**
     * @param $controller Controller
     */
    public function runController($controller)
    {
        $data = $controller->run();
        echo $controller->display($data);
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
        if(!is_array($models)) $models = array($models);
        foreach($models as $model)
        {
            $folder = $this->directory . "/$model";
            $files = scandir($folder);
            foreach($files as $file)
            {
                if(strpos($file, ".php") !== FALSE)
                {
                    $list[] = substr($file, 0, -4);
                }
            }
        }
        return $list;
    }

    public function register_autoload_model()
    {
        $models = $this->config['app']['model'];
        if(!is_array($models)) $models = array($models);

        spl_autoload_register(function($class) use($models)
        {
            foreach($models as $directory)
            {
                $filename = $this->directory . "/" . $directory . "/$class.php";
                if(file_exists($filename))
                {
                    include_once $filename;
                    return true;
                }
            }
        });
    }

    public function log($message, $level = 1)
    {
        echo str_repeat("-", $level) . "> " .$message . "\n";
    }


}