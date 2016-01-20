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
    /**
     * Controller constructor.
     */
    public function __construct()
    {
        $this->config = Bootstrap::getSingleton()->getConfig();
        $this->restoreMessages();
        $this->name = get_class($this);
        $this->template = $this->name . ".twig";

        $this->view = new View($this);
    }

    abstract public function run();

    public function display($data)
    {
        return $this->view->render($data);
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

}