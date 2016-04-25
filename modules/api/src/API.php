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

    /**
     * @param $controller Controller
     */
    public function beforeControllerRun($controller)
    {
        $controller->addMiddleware($this);
    }

    public function next(Request $request, Response $response, $next)
    {
        // TODO: Implement next() method.
        if($request->)
    }
}