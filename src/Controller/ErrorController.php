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
        $this->setTemplate("error.twig");
        $this->response->setResponseCode(501);
    }


    public function run()
    {
        if(isset($_SERVER['HTTP_REFERER']))
            $this->refer = $_SERVER['HTTP_REFERER'];
    }
}