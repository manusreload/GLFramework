<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 22/04/16
 * Time: 13:00
 */

namespace GLFramework\Middleware;


use GLFramework\Middleware;
use GLFramework\Request;
use GLFramework\Response;

class APIAuthorizationMiddleware implements Middleware
{

    public function next(Request $request, Response $response, $next)
    {
        $auth = $request->getHeader("X-Authorization");
        if($auth = $request->getHeader("X-Authorization"))
        {

        }
        else
        {
            throw new \Exception("Please provide an X-Authorization header!");
        }
    }
}