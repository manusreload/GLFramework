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
 * Date: 21/04/16
 * Time: 8:44
 */

namespace GLFramework;

/**
 * Class Request
 *
 * @package GLFramework
 */
class Request
{

    var $params;
    var $method;
    var $uri;

    /**
     * Request constructor.
     *
     * @param $method
     * @param $uri
     */
    public function __construct($method, $uri)
    {
        //        $this->params = $params;
        $this->method = $method;
        $this->uri = $uri;
    }

    /**
     * Obtiene los parametros de peticion
     *
     * @return mixed
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Establecer los parametros de peticion
     *
     * @param mixed $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * Obtiene el metodo de peticion
     *
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Establece el metodo de la meticion (GET, POST, PUT ...)
     *
     * @param mixed $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * Obtiene la url de la peticion
     *
     * @return mixed
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Establecer la url de la peticion
     *
     * @param mixed $uri
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    /**
     * Obtiene las cabeceras de la petición
     *
     * @return string
     */
    public function getHeaders()
    {
        $headers = '';
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) === 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }

    /**
     * Obtiene el header indicado
     *
     * @param $name
     * @return mixed
     */
    public function getHeader($name)
    {
        $headers = $this->getHeaders();
        return $headers[$name];
    }

    /**
     * TODO
     *
     * @return bool
     */
    public function isAjax()
    {
        return strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
