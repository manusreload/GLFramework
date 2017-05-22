<?php

/**
 * Created by PhpStorm.
 * User: mmunoz
 * Date: 22/05/17
 * Time: 13:29
 */
namespace GLFramework\Modules\Debugbar;

use DebugBar\OpenHandler;
use GLFramework\Controller;

class handler extends Controller
{

    /**
     * Implementar aqui el código que ejecutara nuestra aplicación
     * @return mixed
     */
    public function run()
    {
        // TODO: Implement run() method.

        $openHandler = new OpenHandler(Debugbar::getInstance()->getDebugbar());
        $openHandler->handle();
        die();
    }
}