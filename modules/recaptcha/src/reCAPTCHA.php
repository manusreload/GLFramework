<?php

namespace GLFramework\Modules\reCAPTCHA;
use GLFramework\Module\ModuleManager;
use GLFramework\Module\ModuleSource;

/**
 * Created by PhpStorm.
 * User: mmuno
 * Date: 26/09/2016
 * Time: 17:10
 */
class reCAPTCHA extends ModuleSource
{
    public function __construct()
    {
        parent::__construct("recaptcha");
    }


    function displayScripts()
    {
        echo "<script src='https://www.google.com/recaptcha/api.js'></script>";
    }

    function validateCaptha()
    {
        $value = $_REQUEST['g-recaptcha-response'];
        $request = array();
        $request['secret'] = $this->config['secret'];
        $request['response'] = $value;
        $request['remoteip'] = $_SERVER['REMOTE_ADDR'];
        $response = post('https://www.google.com/recaptcha/api/siteverify', $request);
        $json = json_decode($response, true);
        if($json['success'])
        {
            return true;
        }
        return false;
    }
}