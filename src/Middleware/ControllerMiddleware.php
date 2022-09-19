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

/**
 * Class ControllerMiddleware
 *
 * @package GLFramework\Middleware
 */
class ControllerMiddleware implements Middleware
{
    /**
     * @var Controller
     */
    var $controller;

    /**
     * TODO
     *
     * ControllerMiddleware constructor.
     * @param Controller $controller
     */
    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
    }

    /**
     * TODO
     *
     * @param Request $request
     * @param Response $response
     * @param $next
     */
    public function next(Request $request, Response $response, $next)
    {
        // TODO: Implement next() method.

        Events::dispatch('beforeControllerRun', array($this->controller));
        $data = $this->controller->run();
        // $data = call_user_func_array(array($this->controller, 'run'), $request->params);
        $next($request, $response);
        Events::dispatch('afterControllerRun', array($this->controller, $this->controller->response));
        Events::dispatch('beforeViewDisplay', array($this->controller));
        $t0 = microtime(true);
        $response->setContent($this->controller->display($data, $request->params));
        Events::dispatch('afterViewDisplay', array($this->controller, $t0));
    }
}
