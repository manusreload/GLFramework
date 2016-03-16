<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 13/1/16
 * Time: 16:33
 */

namespace GLFramework;


use GLFramework\Module\Module;
use GLFramework\Module\ModuleManager;
use GLFramework\Upload\Uploads;

abstract class Controller
{

    var $name = null;
    var $admin = false;

    private $template = null;
    private $db = null;
    private $view;
    var $messages = array();
    var $config = array();
    var $description = "";
    var $directory;
    /**
     * @var Module
     */
    var $module;
    var $params = array();

    var $redirect = false;
    /**
     * @var Response
     */
    var $response;

    /**
     * Controller constructor.
     * @param $module Module
     * @param string $base
     * @internal param array $config
     */
    public function __construct($base = "", $module = null)
    {
        if($module == null)
            $module = ModuleManager::getInstance()->getMainModule();

        $this->module = $module;
        $this->config = $this->module->getConfig();
        $this->restoreMessages();
        if(empty($this->name))
            $this->name = get_class($this);
        $this->directory = dirname($base);
        $base = substr($base, 0, strrpos($base, "."));
        $this->template = $base . ".twig";

        $this->view = new View($this);
        $this->response = new Response();
    }

    abstract public function run();

    public function display($data, $params)
    {
        return $this->view->render($data, $params);
    }

    public function call($params)
    {
        $this->params = $params;
        $data = call_user_func_array(array($this, "run"), $params);
        Events::fire('afterControllerRun', array($this));
        $this->response->setContent($this->display($data, $params));
        return $this->response;
    }

    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return null|string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param null|string $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    public function redirection($url)
    {
        $this->response->setRedirection($url);
    }

    public function restoreMessages()
    {
        if(isset($_SESSION['messages']))
        {
            $this->messages = $_SESSION['messages'];
            unset($_SESSION['messages']);
        }
    }
    public function shareMessages()
    {
        $_SESSION['messages'] = $this->messages;
    }

    public function quit($redirection = null)
    {
        if($redirection) $this->redirection($redirection);
        $this->shareMessages();
        if(!GL_TESTING)
        {
            Bootstrap::dispatch($this->response);
            exit;
        }
    }

    /**
     * @return DBConnection
     */
    public function getDb()
    {
        if(!$this->db)
            $this->db = new DatabaseManager();
        return $this->db;
    }

    public function addMessage($message, $type = "success")
    {
        Events::fire('onMessageDisplay', array('message' => $message, 'type' => $type));
        $this->messages[] = array('message' => $message, 'type' => $type);
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @return View
     */
    public function getView()
    {
        return $this->view;
    }

    public function getLink($controller, $params = array(), $fullPath = false)
    {
        if($controller instanceof Controller) $controller = get_class($controller);
        $controller = (string) $controller;
        $url = Bootstrap::getSingleton()->getManager()->getRouter()->generate($controller, $params);
        if($fullPath)
        {
            $protocol = "http";
            if(strpos($_SERVER['SCRIPT_URI'], "https") !== FALSE) $protocol = "https";
            return $protocol . "://" . $_SERVER['HTTP_HOST'] . $url;
        }
        return $url;
    }

    /**
     * @param $name
     * @param null $module Module
     * @return string
     */
    public function getResource($name, $module = null)
    {
        if($module == null) $module = $this->module;
        if(is_string($module)) $module = ModuleManager::getModuleInstanceByName($module);
        $config = $module->getConfig();
        $folders = $config['app']['resources'];
        if(!is_array($folders)) $folders = array($folders);
        foreach($folders as $folder)
        {
            $path = $module->getDirectory() . "/" . $folder . "/" . $name;
            if(file_exists($path))
            {
                $path = realpath($path);
                $base = dirname($_SERVER['SCRIPT_FILENAME']);
                $index = strpos($path, $base);
                $url = substr($path, $index + strlen($base));
                $protocol = "http";
                if(strpos($_SERVER['SCRIPT_URI'], "https") !== FALSE) $protocol = "https";

                return $protocol . "://" . $_SERVER['SERVER_NAME'] . $url;
            }
        }
    }

    public function getUploads()
    {
        return new Uploads($this->module->getDirectory(), $this->config);
    }

    public function setContentType($type)
    {
        $this->response->setContentType($type);
    }

    public function log($message, $level)
    {
        Events::fire('onLog', array('message' => $message, 'level' => $level));
    }

}