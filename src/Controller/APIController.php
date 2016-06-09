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
 * Time: 12:59
 */

namespace GLFramework\Controller;


use GLFramework\Controller;
use GLFramework\Middleware\APIAuthorizationMiddleware;
use GLFramework\Model;
use GLFramework\ModelResult;

class APIController extends Controller
{
    /**
     * @var \User
     */
    var $user;
    /**
     * APIController constructor.
     * @param string $base
     * @param Module|null $module
     */
    public function __construct($base, $module)
    {
        parent::__construct($base, $module);
        $this->addMiddleware(new APIAuthorizationMiddleware());
    }


    public function run()
    {
        // TODO: Implement run() method.
        switch ($_SERVER['REQUEST_METHOD'])
        {
            case 'GET':
                return $this->get($this->params);
            case 'POST':
                return $this->post($this->params);
            case 'PUT':
                return $this->put($this->params);
            case 'DELETE':
                return $this->delete($this->params);
        }
    }

    /**
     * @param $data
     * @param $params
     * @return array|null|string
     */
    public function display($data, $params)
    {
        $result = array();
        $header = array();
        $messages = array();
        foreach ($this->messages as $message)
        {
            $result[$message['type']][] = $message['message'];
        }
        $header['date'] = date("Y-m-d H:i:s");
        $header['controller'] = get_class($this);
//        $result['messages'] = $messages;

        $result['header'] = $header;
        if($data instanceof Model or $data instanceof ModelResult)
        {
            $result['data'] = $data->json();
        }
        else
        {
            $result['data'] = $data;
        }
        return json_encode($result);
    }

    public function post($params)
    {
        return false;
    }

    public function get($params)
    {
        return false;
    }

    public function put($params)
    {
        return false;
    }
    public function delete($params)
    {
        return false;
    }

    public function getData($json = true, $assoc = false)
    {
        $data =  file_get_contents("php://input");
        if(!$json) return $data;
        return json_decode($data, $assoc);
    }


}