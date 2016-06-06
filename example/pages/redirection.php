<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 19/1/16
 * Time: 19:11
 */

class redirection extends \GLFramework\Controller
{

    public function run()
    {
        $this->addMessage("Now redirect!");
        $this->setTemplate(null);
        $this->quit("/home");

    }
}