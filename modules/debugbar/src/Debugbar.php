<?php

namespace GLFramework\Modules\Debugbar;
use DebugBar\Bridge\Twig\TraceableTwigEnvironment;
use DebugBar\Bridge\Twig\TwigCollector;
use DebugBar\DataCollector\ConfigCollector;
use DebugBar\DataCollector\MessagesCollector;
use DebugBar\DataCollector\PDO\PDOCollector;
use DebugBar\DataCollector\PDO\TraceablePDO;
use DebugBar\DataCollector\TimeDataCollector;
use DebugBar\StandardDebugBar;
use GLFramework\Bootstrap;
use GLFramework\Controller;
use GLFramework\Database\MySQLConnection;
use GLFramework\DatabaseManager;
use GLFramework\Module\ModuleManager;
use GLFramework\Response;
use GLFramework\View;

/**
 * Created by PhpStorm.
 * User: manus
 * Date: 14/03/16
 * Time: 12:48
 */
class Debugbar
{
    /**
     * @var \DebugBar\DebugBar
     */
    private static $debugbar;
    /**
     * @var TimeDataCollector
     */
    private $time;
    /**
     * @var MessagesCollector
     */
    private $messages;

    /**
     * Debugbar constructor.
     */
    public function __construct()
    {
        $this->time = $this->getDebugbar()->getCollector('time');
        $this->messages = $this->getDebugbar()->getCollector('messages');
    }

    public function getDebugbar()
    {
        if(self::$debugbar == null)
        {
            self::$debugbar = new StandardDebugBar();
        }
        return self::$debugbar;
    }

    /**
     * @param $instance Controller
     */
    public function beforeControllerRun($instance)
    {
        $config = Bootstrap::getSingleton()->getConfig();
        if(isset($config['database']['database']))
        {
            $db = new DatabaseManager();
        }
        if(!$this->getDebugbar()->hasCollector('config'))
            $this->getDebugbar()->addCollector(new ConfigCollector(Bootstrap::getSingleton()->getConfig()));
        $this->time->startMeasure('controller', 'Controller process time');
    }
    /**
     * @param $instance Controller
     */
    public function afterControllerRun($instance, $response)
    {
        $this->time->stopMeasure('controller');

    }

    /**
     * @param $response Response
     */
    public function beforeResponseSend($response)
    {

        if($response->getAjax())
        {
            $this->getDebugbar()->sendDataInHeaders();
        }
    }

    public function onCoreStartUp($time)
    {
        $this->time->addMeasure('Core start up', $time, microtime(true));
        $this->time->startMeasure('run', 'Core run finished');
    }

    /**
     * @param $render View
     */
    public function displayStyle($render)
    {
        $render = $this->getDebugbar()->getJavascriptRenderer();
        $this->time->stopMeasure('run');
        if(Bootstrap::isDebug())
        {
            echo $render->renderHead();
        }
    }
    /**
     * @param $render View
     */
    public function displayScripts($render)
    {
        $render = $this->getDebugbar()->getJavascriptRenderer();
        if(Bootstrap::isDebug())
        {
            echo $render->render();
        }
    }

    public function onPDOCreated(&$pdo)
    {
        $pdo = new TraceablePDO($pdo);
        $this->getDebugbar()->addCollector(new PDOCollector($pdo, $this->time));
    }

    public function onViewCreated(&$twig)
    {
        $twig = new TraceableTwigEnvironment($twig, $this->time);
        if(!$this->getDebugbar()->hasCollector('twig'))
            $this->getDebugbar()->addCollector(new TwigCollector($twig));
    }

    public function onLog($message,  $level)
    {
        $this->messages->addMessage($message, $level);
    }
}