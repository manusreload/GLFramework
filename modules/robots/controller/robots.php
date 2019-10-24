<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 24/05/16
 * Time: 10:03
 */

namespace GLFramework\Modules\Robots;


use GLFramework\Bootstrap;
use GLFramework\Controller;

class robots extends Controller
{

    /**
     * Implementar aqui el código que ejecutara nuestra aplicación
     * @return mixed
     */
    public function run()
    {
        $this->setContentType("text/plain");
    }
}