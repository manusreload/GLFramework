<?php

/**
 * Created by PhpStorm.
 * User: manus
 * Date: 29/03/16
 * Time: 13:11
 */
class ControllerTest extends PHPUnit_Framework_TestCase
{


    /**
     *
     */
    public function testCreateController()
    {
        $bootstrap = BootstrapTest::testCreateBootstrap();
        $bootstrap->init();

    }
}
