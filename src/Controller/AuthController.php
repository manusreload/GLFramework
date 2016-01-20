<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 13/1/16
 * Time: 19:28
 */

namespace GLFramework\Controller;


use GLFramework\Controller;
use GLFramework\Model\User;

class AuthController extends Controller
{

    /**
     * @var User
     */
    var $user;
    private $requireLogin = true;
    public function run()
    {
        // TODO: Implement run() method.
        if(isset($_SESSION['user']))
        {
            $db = $this->getDb();
            $username =  $_SESSION['user'][0];
            $password =  $_SESSION['user'][1];
            $user = $db->select_first("SELECT * FROM tbl_user WHERE user_name = '$username' AND password = '$password'");
            if($user)
            {
                $this->user = new User($user);
            }
            else{
                unset($_SESSION['user']);
            }


        }
        if(isset($_GET['logout']))
        {
            $this->addMessage("Se ha desconectado correctamente");
            unset($_SESSION['user']);
        }
        if($this->requireLogin)
        {
            if(!isset($_SESSION['user']))
            {
                if(strpos($_SERVER['REQUEST_URI'], "/login") === FALSE)
                {
                    if(!isset($_GET['logout']))
                        $this->addMessage("Por favor acceda con su cuenta antes de continuar", "warning");
                    $this->quit($this->config['app']['basepath'] . "/login");
                }
            }
        }
    }

    public function processLogin()
    {
        if(isset($_POST['username']) && isset($_POST['password']))
        {
            $db = $this->getDb();
            $username = $db->escape_string($_POST['username']); // Para evitar inyecciones SQL
            $password = md5($_POST['password']);

            $user = $db->select_first("SELECT * FROM tbl_user WHERE user_name = '$username' AND password = '$password'");
            if($user)
            {
                $_SESSION['user'] = array($username, $password);
                $this->quit("home");
            }
            else{
                return array("error" => true);
            }
        }
        return false;
    }

    /**
     * @return boolean
     */
    public function isRequireLogin()
    {
        return $this->requireLogin;
    }

    /**
     * @param boolean $requireLogin
     */
    public function setRequireLogin($requireLogin)
    {
        $this->requireLogin = $requireLogin;
    }


}