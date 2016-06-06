<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 22/04/16
 * Time: 9:23
 */

namespace GLFramework\Modules\CSRF;


use GLFramework\Middleware;
use GLFramework\Request;
use GLFramework\Response;

class CSRFValidationMiddleware implements Middleware
{

    var $csrf;

    /**
     * CSRFValidationMiddleware constructor.
     */
    public function __construct()
    {
        $this->csrf = new CSRF();
    }


    public function next(Request $request, Response $response, $next)
    {
        // TODO: Implement next() method.
        if($request->getMethod() == "POST")
        {
            $this->csrf->validateCSRF();
        }
        $next($request, $response);
    }
}