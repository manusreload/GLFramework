<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 25/04/16
 * Time: 9:42
 */

namespace GLFramework\Modules\API;


use GLFramework\Controller;
use GLFramework\Middleware;
use GLFramework\Request;
use GLFramework\Response;

class API implements Middleware
{
    
    var $controller;
    /**
     * @param $controller Controller
     */
    public function afterControllerConstruct($controller)
    {
        if($controller instanceof Controller\APIController)
        {
            $this->controller = $controller;
            $controller->addMiddleware($this);
        }
    }

    public function next(Request $request, Response $response, $next)
    {
        // TODO: Implement next() method.
        if($auth = $request->getHeader("X-Authorization"))
        {
            if($this->authorize($auth))
            {
                $next($request, $response);
            }
            else
            {
                $response->setContent(json_encode(array('error' => "X-Authorization not valid, please set an valid authorization.")));
            }
        }
        else
        {
            if($request->isAjax())
            {
                $login = new Controller\AuthController();
                $login->onCreate();
                if($login->login())
                {
                    $this->controller->user = $login->user;
                    $next($request, $response);
                }
            }
            else
            {

                $response->setContent(json_encode(array('error' => "Please provide an X-Authorization header!")));
            }
        }
    }
    
    public function authorize($auth)
    {
        $apiAuth = new \APIAuthorization();
        $result = $apiAuth->get(array('token' => $auth));
        if($result->count() == 1)
        {
            $apiAuth = $result->getModel();
            if($apiAuth->id)
            {
                if($apiAuth->id_user)
                {
                    $this->controller->user = Controller\AuthController::auth($apiAuth->id_user);
                }
            }
            return true;
        }
        return false;
    }

    public function getAdminControllers()
    {
        return 'GLFramework\Modules\Controller\API\api';
    }
}