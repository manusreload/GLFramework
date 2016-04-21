<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 21/04/16
 * Time: 8:43
 */

namespace GLFramework;


interface Middleware
{

    public function next(Request $request, Response $response, $next);
}