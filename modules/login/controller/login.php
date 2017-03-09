<?php

namespace GLFramework\Modules\Login;
use GLFramework\Controller\AuthController;

/**
 * Created by PhpStorm.
 * User: manus
 * Date: 27/5/16
 * Time: 18:36
 */
class login extends AuthController
{

    var $versions;
    var $result;
    public function run()
    {
        // TODO: Implement run() method.
        if(!parent::run()) return false;
        if(isset($_POST['login']))
        {
            if($this->processLogin())
            {
//                $this->redirection("/panel");
            }
        }

    }
}