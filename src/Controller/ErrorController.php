<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 13/1/16
 * Time: 17:50
 */

namespace GLFramework\Controller;


use GLFramework\Controller;

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
    }


    public function run()
    {
        // TODO: Implement run() method.
        $this->refer = $_SERVER['HTTP_REFERER'];
        $this->setTemplate("error.twig");
    }
}