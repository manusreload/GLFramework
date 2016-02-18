<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 13/1/16
 * Time: 16:33
 */

namespace GLFramework;


abstract class Controller
{

    private $name = null;
    private $template = null;
    private $db = null;
    private $view;
    var $messages = array();
    var $config = array();
    var $description = "";

    /**
     * Controller constructor.
     * @param string $base
     */
    public function __construct($base = "")
    {
        $this->config = Bootstrap::getSingleton()->getConfig();
        $this->restoreMessages();
        $this->name = get_class($this);
        $base = substr($base, 0, strrpos($base, "."));
        $this->template = $base . ".twig";

        $this->view = new View($this);
    }

    abstract public function run();

    public function display($data, $params)
    {
        return $this->view->render($data, $params);
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
        header("Location: $url");
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
        exit;
    }

    /**
     * @return DBConnection
     */
    public function getDb()
    {
        if(!$this->db)
            $this->db = new DBConnection();
        return $this->db;
    }

    public function addMessage($message, $type = "success")
    {
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

    public function getLink($controller, $params = array())
    {
        $controller = (string) $controller;
        return Bootstrap::getSingleton()->getManager()->getRouter()->generate($controller, $params);
    }

}