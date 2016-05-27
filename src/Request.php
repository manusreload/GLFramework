<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 21/04/16
 * Time: 8:44
 */

namespace GLFramework;


class Request
{

    var $params;
    var $method;
    var $uri;

    /**
     * Request constructor.
     * @param $params
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
     * @return mixed
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Establecer los parametros de peticion
     * @param mixed $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * Obtiene el metodo de peticion
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Establece el metodo de la meticion (GET, POST, PUT ...)
     * @param mixed $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * Obtiene la url de la peticion
     * @return mixed
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Establecer la url de la peticion
     * @param mixed $uri
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    /**
     * Obtiene las cabeceras de la petición
     * @return string
     */
    public function getHeaders()
    {
        $headers = '';
        foreach ($_SERVER as $name => $value)
        {
            if (substr($name, 0, 5) == 'HTTP_')
            {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }

    /**
     * Obtiene el header indicado
     * @param $name
     * @return mixed
     */
    public function getHeader($name)
    {
        $headers = $this->getHeaders();
        return $headers[$name];
    }

    public function isAjax()
    {
        return strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == "xmlhttprequest";
    }


}