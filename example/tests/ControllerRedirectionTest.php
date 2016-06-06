<?php

/**
 * Created by PhpStorm.
 * User: manus
 * Date: 16/03/16
 * Time: 12:49
 */
class ControllerRedirectionTest extends \GLFramework\Tests\TestCase
{

    public function testRun()
    {
        $this->visit("/redirection")->see("Test successful!");
    }

}