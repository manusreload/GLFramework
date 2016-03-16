<?php

/**
 * Created by PhpStorm.
 * User: manus
 * Date: 16/03/16
 * Time: 9:58
 */
class ControllerHomeTest extends \GLFramework\Tests\TestCase
{


    public function testHome()
    {
        $this->visit("/home")
            ->see("Hola mundo")
            ->click("URL Parametrized")
            ->see("Hola mundo")
            ->click('Sub home')->see('Sub content')->dontSee("Hola mundo");
    }
}