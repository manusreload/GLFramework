<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 1/03/16
 * Time: 13:04
 */

namespace GLFramework\Modules\Pacman;


use GLFramework\Controller;
use GLFramework\Module\ModuleManager;
use GLFramework\View;

class pacman extends Controller
{
    public function __construct($base = "", $module = null)
    {
        parent::__construct($base, $module);
    }




    public function run()
    {
        // TODO: Implement run() method.
        throw new \Exception("Force pacman view");
    }

    /**
     * @param $view View
     */
    public function displayExtraErrorView($view)
    {
//        $view->getTwig()->enableDebug();
        return $view->getTwig()->render('pacman/views/custom_error.twig');

    }

    /**
     * @param $view View
     * @return mixed
     */
    public static function displayStyle($view)
    {
        echo '<link href="' . $view->getController()->getResource('style.css', 'pacman') . '" rel="stylesheet" />';
    }
}