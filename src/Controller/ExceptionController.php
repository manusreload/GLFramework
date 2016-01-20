<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 13/1/16
 * Time: 17:53
 */

namespace GLFramework\Controller;


class ExceptionController extends ErrorController
{

    private $exception;
    var $trace = null;

    /**
     * ExceptionController constructor.
     * @param $exception \Exception
     */
    public function __construct($exception)
    {
        $this->exception = $exception;
        $this->trace = $exception->getTraceAsString();
        parent::__construct($exception->getMessage());
    }

}