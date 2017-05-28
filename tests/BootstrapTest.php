<?php

/**
 * Created by PhpStorm.
 * User: manus
 * Date: 29/03/16
 * Time: 12:44
 */

/**
 * Class BootstrapTest
 */
class BootstrapTest extends PHPUnit_Framework_TestCase
{
    /**
     * TODO
     *
     * @return \GLFramework\Bootstrap
     */
    public static function testCreateBootstrap()
    {
        return new \GLFramework\Bootstrap(__DIR__ . '/data/');
    }

    /**
     * TODO
     *
     * @expectedException Symfony\Component\Yaml\Exception\ParseException
     */
    public function testInvalidBootstrap()
    {
        new \GLFramework\Bootstrap(__DIR__ . '/data', 'invalidConfig.yml');
    }

    /**
     * TODO
     *
     * @depends testCreateBootstrap
     * @param $bootstrap \GLFramework\Bootstrap
     */
    public function testConfig($bootstrap)
    {
        $config = $bootstrap->getConfig();
        $this->assertArrayHasKey('app', $config);
        $this->assertArrayHasKey('index', $config['app']);
        $this->assertArrayHasKey('database', $config);
        $this->assertArrayNotHasKey('modules', $config);
        $this->assertEquals('home', $config['app']['index']);
        $this->assertEquals('127.0.0.1', $config['database']['hostname']);
    }

    /**
     * TODO
     *
     * @depends testCreateBootstrap
     * @param $bootstrap \GLFramework\Bootstrap
     */
    public function testTestConfig($bootstrap)
    {
        $bootstrap->setupTest();
        $config = $bootstrap->getConfig();
        $this->assertArrayHasKey('app', $config);
        $this->assertArrayHasKey('index', $config['app']);
        $this->assertArrayHasKey('database', $config);
        $this->assertArrayHasKey('modules', $config);
        $this->assertEquals('home', $config['app']['index']);
        $this->assertEquals('localhost', $config['database']['hostname']);
    }

    /**
     * TODO
     *
     * @depends testCreateBootstrap
     * @param $bootstrap \GLFramework\Bootstrap
     */
    public function testIncludeConfig($bootstrap)
    {
        $config = $bootstrap->getConfig();
        $this->assertArrayHasKey('key_in_include', $config);
    }
}
