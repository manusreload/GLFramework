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

    /**
     * Request constructor.
     * @param $params
     */
    public function __construct($params)
    {
        $this->params = $params;
    }


}