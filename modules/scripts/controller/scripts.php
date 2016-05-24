<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 24/05/16
 * Time: 10:03
 */

namespace GLFramework\Modules\Scripts;


use GLFramework\Controller;

class scripts extends Controller
{

    /**
     * Implementar aqui el código que ejecutara nuestra aplicación
     * @return mixed
     */
    public function run()
    {
        global $controller;
        $controller = $this;
        // TODO: Implement run() method.
        $this->getResponse()->setContentType("text/plain");
        $script = $this->params['script'];
        $filename = __DIR__ . "/../scripts/" . $script . ".php";
        ob_start();
        include $filename;
        ob_end_flush();
        $this->getResponse()->setContent(ob_get_contents());
        $this->setTemplate(null);
    }
}