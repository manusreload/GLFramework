<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 10/5/17
 * Time: 21:26
 */

namespace GLFramework\Modules\Maintenance;


use GLFramework\Controller;

class home extends Controller
{

    /**
     * Implementar aqui el código que ejecutara nuestra aplicación
     * @return mixed
     */
    public function run()
    {
        // TODO: Implement run() method.
    }

    public function beforeControllerRun($controller)
    {
        die("OK");
    }
}