<?php

/**
 * Created by PhpStorm.
 * User: manus
 * Date: 29/03/16
 * Time: 13:11
 */

/**
 * Class ControllerTest
 */
class ControllerTest extends PHPUnit_Framework_TestCase
{
    /**
     * TODO
     *
     * @return \GLFramework\Bootstrap
     */
    public function testCreateBootstrap()
    {
        $bootstrap = BootstrapTest::testCreateBootstrap();
        $bootstrap->overrideConfig(__DIR__ . '/data/config.modules.yml');
        $bootstrap->init();
        return $bootstrap;
    }

    /**
     * TODO
     *
     * @depends testCreateBootstrap
     * @param $bootstrap \GLFramework\Bootstrap
     */
    public function testCreateController($bootstrap)
    {
        $this->assertTrue($bootstrap->getManager()->exists('test'));
        $controller = \GLFramework\Module\ModuleManager::instanceController('controller_test');
        $this->assertEquals('this is a test', $controller->run());
    }
}
