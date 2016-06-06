<?php

/**
 * Created by PhpStorm.
 * User: manus
 * Date: 29/03/16
 * Time: 13:20
 */
class ModuleTest extends PHPUnit_Framework_TestCase
{


    public function testModule()
    {
        $bootstrap = BootstrapTest::testCreateBootstrap();
        $bootstrap->overrideConfig(__DIR__ . "/data/config.modules.yml");
        $bootstrap->init();
        return $bootstrap->getManager();
    }

    /**
     * @depends testModule
     * @param $manager \GLFramework\Module\ModuleManager
     */
    public function testMainModule($manager)
    {
        $this->assertInstanceOf('GLFramework\Module\Module', $manager->getMainModule());
    }

    /**
     * @depends testModule
     * @param $manager \GLFramework\Module\ModuleManager
     */
    public function testModulesInternal($manager)
    {
        $this->assertTrue($manager->exists("admin"));
    }
    /**
     * @depends testModule
     * @param $manager \GLFramework\Module\ModuleManager
     */
    public function testModulesInConfig($manager)
    {
        $this->assertTrue($manager->exists("test"));
    }
    /**
     * @depends testModule
     * @param $manager \GLFramework\Module\ModuleManager
     */
    public function testModulesFolders($manager)
    {
        $this->assertTrue($manager->exists("extra_test"));
    }
}
