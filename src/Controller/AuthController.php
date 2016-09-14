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
 * Time: 19:28
 */

namespace GLFramework\Controller;


use GLFramework\Controller;
use GLFramework\Events;
use GLFramework\Middleware;
use GLFramework\Model\User;
use GLFramework\Request;
use GLFramework\Response;

class AuthController extends Controller implements Middleware
{

    private static $session_key = "auth_user";
    /**
     * @var User
     */
    var $user;
    private $requireLogin = true;

    public function __construct($base, $module)
    {
        parent::__construct($base, $module);
        if(isset($_COOKIE[self::$session_key]))
        {
            $_SESSION[self::$session_key] = unserialize($_COOKIE[self::$session_key]);
        }
        if(isset($_SESSION[self::$session_key]))
        {
            $username =  $_SESSION[self::$session_key][0];
            $password =  $_SESSION[self::$session_key][1];
            $user = self::instanceUser(null);
            $user = $user->getByUserPassword($username, $password);
            if($user)
            {
                $this->user = self::instanceUser($user);
            }
            else{
                unset($_SESSION[self::$session_key]);
            }
        }
        $this->addMiddleware($this);
    }


    public function login()
    {
        // TODO: Implement run() method.

        if(isset($_GET['logout']))
        {
            $this->addMessage("Se ha desconectado correctamente");
            unset($_SESSION[self::$session_key]);
            setcookie(self::$session_key, "", 0);
        }
        if($this->requireLogin)
        {
            if(!isset($_SESSION[self::$session_key]))
            {
                if(strpos($_SERVER['REQUEST_URI'], "/login") === FALSE)
                {
                    if(!isset($_GET['logout']))
                        $this->addMessage("Por favor acceda con su cuenta antes de continuar", "warning");
                    if(!isset($_GET['logout']))
                        $_SESSION['return'] = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                    $this->quit($this->config['app']['basepath'] . "/login");
                    return false;
                }
            }
        }
        return true;
    }

    public function processLogin($username = null, $password = null, $encrypt = true)
    {
        if($username === null)
            $username = $_POST['username'];
        if($password === null)
            $password = $_POST['password'];
        if(isset($username) && isset($password))
        {
            $this->csrf();
            $user = self::instanceUser(null);
            $db = $this->getDb();
            $username = $db->escape_string($username); // Para evitar inyecciones SQL
            if($encrypt)
                $password = $user->encrypt($password);
            $user = $user->getByUserPassword($username, $password);
            if($user)
            {
                $this->user = new User($user);
                $_SESSION[self::$session_key] = array($username, $password);
                Events::fire('onLoginSuccess', array('user' => $this->user));
                if(isset($_REQUEST['remember']) && $_REQUEST['remember'])
                {
                    setcookie(self::$session_key, serialize($_SESSION[self::$session_key]), time() + 60 * 60 * 24 * 30);
                }
                if(isset($_SESSION['return']))
                {
                    $this->redirection($_SESSION['return']);
                }
                else
                {
                    $this->redirection("/home");
                }
                unset($_SESSION['return']);
                return true;
            }
            else{
                $this->addMessage("Usuario o contraseña incorrecta", "danger");
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

    /**
     * @param $data
     * @return User
     */
    public static function instanceUser($data = null)
    {
        return User::newInstance('User', $data);
    }

    public function next(Request $request, Response $response, $next)
    {
        if($this->login())
        {
            $next($request, $response);
        }
        // TODO: Implement next() method.
    }

    public function run()
    {
        // Por motivos de compativilidad.
        // Lo ideal es que esta clase sea abstracta
        return true;
    }

    public static function auth($user_id)
    {
        $user = self::instanceUser($user_id);
        $_SESSION[self::$session_key] = array($user->user_name, $user->password);
        return $user;
    }
}