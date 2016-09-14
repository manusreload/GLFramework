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