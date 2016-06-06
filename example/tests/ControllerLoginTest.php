<?php

/**
 * Created by PhpStorm.
 * User: manus
 * Date: 16/03/16
 * Time: 13:36
 */
class ControllerLoginTest extends \GLFramework\Tests\TestCase
{

    public function testLogin()
    {
        $this->requireDatabase();

        $user = new User();
        $user->user_name = "test";
        $user->email = "test@email.com";
        $user->password = $user->encrypt("insecure");
        $user->nombre = "Pruebas";
        $user->privilegios = "Usuario";
        $this->assertTrue($user->save(true) > 0, "Can not create a test user");

        $this->visit("/login")->see('Sign in')
            ->submitForm("Sign in", array('username' => 'test', 'password' => "bad password" ))
            ->see('incorrecta')
            ->submitForm("Sign in", array('username' => 'test', 'password' => "insecure" ))
            ->dontSee("incorrecta")->see('Hola mundo');

        $this->removeLater($user);
    }
}