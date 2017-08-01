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

    /**
     * @param $controller Controller
     */
    public function beforeControllerRun($controller)
    {
        if($this->config['maintenance'] && $controller->admin == false) {
            die($this->getView()->display('maintenance.twig'));
        }
        if(isset($this->config['maintenanceMessage']) && $this->config['maintenanceMessage']) {
            $controller->addMessage($this->config['maintenanceMessage'], 'info');
        }
//        if($this->config[''])
//        die("OK");
    }
}