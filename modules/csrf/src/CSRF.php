<?php

namespace GLFramework\Modules\CSRF;
use DebugBar\Bridge\Twig\TraceableTwigEnvironment;
use DebugBar\Bridge\Twig\TwigCollector;
use DebugBar\DataCollector\ConfigCollector;
use DebugBar\DataCollector\PDO\PDOCollector;
use DebugBar\DataCollector\PDO\TraceablePDO;
use DebugBar\DataCollector\TimeDataCollector;
use DebugBar\StandardDebugBar;
use GLFramework\Bootstrap;
use GLFramework\Controller;
use GLFramework\Database\MySQLConnection;
use GLFramework\DatabaseManager;
use GLFramework\Module\ModuleManager;
use GLFramework\View;

/**
 * Created by PhpStorm.
 * User: manus
 * Date: 14/03/16
 * Time: 12:48
 */
class CSRF
{
    /**
     * @param $instance Controller
     */
    public function beforeControllerRun($instance)
    {
        $instance->getView()->getTwig()->addFunction(new \Twig_SimpleFunction('csrf_token', function()
        {
            return \CSRF::generate()->token;
        }));
    }

    public function validateCSRF()
    {
        if(isset($_REQUEST['_token']))
        {
            $csrf = new \CSRF();
            $model = $csrf->get(array('token' => $_REQUEST['_token']))->getModel();
            if($model->id)
            {
                if($model->used == 1)
                {
                    $this->throwException("Ya se ha enviado este token de validacion.");
                }
                else
                {
                    $model->used = 1;
                    $model->save();
                }
            }
            else
            {
                $this->throwException("No se ha generado el token de validacion.");
            }
        }
        else
        {
            $this->throwException("No se encuentra el token de validación.");
        }

    }

    private function throwException($message = "")
    {
        new CSRFTokenVerificationFailedException($message);
    }
}