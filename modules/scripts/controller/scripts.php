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
        // TODO: Implement run() method.
        $script = $this->params['script'];
        print_debug($script);
    }
}