<?php
namespace GLFramework\Middleware;
use GLFramework\Controller;
use GLFramework\Events;
use GLFramework\Middleware;
use GLFramework\Request;
use GLFramework\Response;

/**
 * Created by PhpStorm.
 * User: manus
 * Date: 21/04/16
 * Time: 9:04
 */
class ControllerMiddleware implements Middleware
{

    /**
     * @var Controller
     */
    var $controller;

    /**
     * ControllerMiddleware constructor.
     * @param Controller $controller
     */
    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
    }


    public function next(Request $request, Response $response, $next)
    {
        // TODO: Implement next() method.

        Events::fire('beforeControllerRun', array($this->controller));
        $data = call_user_func_array(array($this->controller, "run"), $request->params);
        $next($request, $response);
        Events::fire('afterControllerRun', array($this->controller, $this->response));
        $response->setContent($this->controller->display($data, $request->params));
    }
}