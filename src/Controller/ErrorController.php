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
 * Time: 17:50
 */

namespace GLFramework\Controller;


use GLFramework\Controller;
use GLFramework\Events;

class ErrorController extends Controller
{

    var $error;

    var $refer;
    /**
     * ErrorController constructor.
     * @param $error
     */
    public function __construct($error)
    {
        parent::__construct();
        $this->error = $error;
        $this->setTemplate("error.twig");
        $this->response->setResponseCode(501);
    }


    public function run()
    {
        if(isset($_SERVER['HTTP_REFERER']))
            $this->refer = $_SERVER['HTTP_REFERER'];
        Events::fire('onError', array('error' => $this->error, 'refer' => $this->refer));
    }
}