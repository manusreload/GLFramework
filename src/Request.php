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
     * @return mixed
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param mixed $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param mixed $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @return mixed
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param mixed $uri
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
    }



}