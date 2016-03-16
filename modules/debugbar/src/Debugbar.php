<?php

namespace GLFramework\Modules\Debugbar;
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
     * Debugbar constructor.
     */
    public function __construct()
    {
        $this->time = $this->getDebugbar()->getCollector('time');
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
        $db = new DatabaseManager();
        $this->getDebugbar()->addCollector(new ConfigCollector(Bootstrap::getSingleton()->getConfig()));
        $this->time->startMeasure('controller', 'Controller process time');
    }
    /**
     * @param $instance Controller
     */
    public function afterControllerRun($instance)
    {
        $this->time->stopMeasure('controller');
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
        $this->getDebugbar()->addCollector(new TwigCollector($twig));
    }
}