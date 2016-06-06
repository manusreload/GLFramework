<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 22/04/16
 * Time: 13:00
 */

namespace GLFramework\Middleware;


use GLFramework\Events;
use GLFramework\Middleware;
use GLFramework\Request;
use GLFramework\Response;

class APIAuthorizationMiddleware implements Middleware
{

    public function next(Request $request, Response $response, $next)
    {
        $response->setContentType("text/json");
        $next($request, $response);
        
    }
}