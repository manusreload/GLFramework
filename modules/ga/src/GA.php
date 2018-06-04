<?php

namespace GLFramework\Modules\GoogleAnalytics;
use DebugBar\Bridge\SwiftMailer\SwiftLogCollector;
use DebugBar\Bridge\SwiftMailer\SwiftMailCollector;
use DebugBar\Bridge\Twig\TraceableTwigEnvironment;
use DebugBar\Bridge\Twig\TwigCollector;
use DebugBar\DataCollector\ConfigCollector;
use DebugBar\DataCollector\MessagesCollector;
use DebugBar\DataCollector\PDO\PDOCollector;
use DebugBar\DataCollector\TimeDataCollector;
use DebugBar\StandardDebugBar;
use GLFramework\Bootstrap;
use GLFramework\Controller;
use GLFramework\Database\MySQLConnection;
use GLFramework\DatabaseManager;
use GLFramework\Model\User;
use GLFramework\Module\Module;
use GLFramework\Module\ModuleManager;
use GLFramework\Modules\Debugbar\Collectors\TraceablePDO;
use GLFramework\Response;
use GLFramework\View;

/**
 * Created by PhpStorm.
 * User: manus
 * Date: 14/03/16
 * Time: 12:48
 */
class GA
{


    var $config = array();
    /**
     * GA constructor.
     * @param $module Module
     */
    public function __construct($module)
    {
        $this->config = $module->getConfig();
    }

    public function onMessageDisplay($message, $type)
    {
        $params = array();
        $params['ec'] = "message";    // Categoria
        $params['ea'] = $message;    // Action
        $params['el'] = $type;    // Label
//        $params['ev'] = $type;    // Valor
        $this->collect($params);
    }

    /**
     * @param $user User
     */
    public function onLoginSuccess($user)
    {
        $params = array();
        $params['ec'] = "login";    // Categoria
        $params['ea'] = $user->nombre;    // Action
        $params['el'] = $user->user_name;    // Label
        $params['ev'] = $user->id;    // Valor
        $this->collect($params);
    }
    public function onError($error, $refer)
    {
        $params = array();
        $params['ec'] = "error";    // Categoria
        $params['ea'] = $error;    // Action
        $params['el'] = $refer;    // Label
        $this->collect($params);
    }


    public function collect($params = array(), $type = "event")
    {
        if (isset($_COOKIE['_ga'])) {
            $params['v'] = "1";
            $params['t'] = $type;
            $params['tid'] = $this->config['tracker'];
            $params['cid'] = $_COOKIE['_ga'];
            $params['dr'] = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            post('https://www.google-analytics.com/collect', $params);
        }
    }

    /**
     * @param $render View
     * @return mixed
     */
    public function addTrackerJS($render) {
        return $render->display('ga.twig', array('config' => $this->config));
    }
}