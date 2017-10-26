<?php

namespace GLFramework\Modules\Debugbar;
use DebugBar\Bridge\SwiftMailer\SwiftLogCollector;
use DebugBar\Bridge\SwiftMailer\SwiftMailCollector;
use DebugBar\Bridge\Twig\TraceableTwigEnvironment;
use DebugBar\Bridge\Twig\TwigCollector;
use DebugBar\DataCollector\ConfigCollector;
use DebugBar\DataCollector\MessagesCollector;
use DebugBar\DataCollector\PDO\PDOCollector;
use DebugBar\DataCollector\PDO\TraceablePDO;
use DebugBar\DataCollector\TimeDataCollector;
use DebugBar\Storage\FileStorage;
use GLFramework\Events;
use GLFramework\Filesystem;
use GLFramework\Modules\Debugbar\Collectors\ControllerCollector;
use GLFramework\Modules\Debugbar\Collectors\ErrorCollector;
use GLFramework\Modules\Debugbar\Collectors\MySQLiCollectorextends;
use GLFramework\Modules\Debugbar\Collectors\RequestDataCollector;
use GLFramework\Modules\Debugbar\Collectors\ResponseCollector;
use DebugBar\DataCollector\PhpInfoCollector;
use DebugBar\DataCollector\MemoryCollector;
use DebugBar\DataCollector\ExceptionsCollector;
use DebugBar\StandardDebugBar;
use GLFramework\Bootstrap;
use GLFramework\Controller;
use GLFramework\Database\MySQLConnection;
use GLFramework\DatabaseManager;
use GLFramework\Module\ModuleManager;
use GLFramework\Response;
use GLFramework\View;
use GLFramework\Module\Module;

/**
 * Created by PhpStorm.
 * User: manus
 * Date: 14/03/16
 * Time: 12:48
 */
class Debugbar
{

    private $config;
    private static $instance;
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
     * @var RequestDataCollector
     */
    private $request;

    /**
     * @var ControllerCollector
     */
    private $controller;
    /**
     * @var ResponseCollector
     */
    private $response;

    /**
     * @var ExceptionsCollector
     */
    private $exceptions;
    /**
     * @var ErrorCollector
     */
    private $errors;

    private $stop = false;

    /**
     * Debugbar constructor.
     * @param null|Module $args
     * @throws \DebugBar\DebugBarException
     */
    public function __construct($args = null)
    {
        self::$instance = $this;
        $debugbar = $this->getDebugbar();
        $config = $args->getConfig();
        if($config['filesystem'])
        {
            $fs = new Filesystem("debugbar");
            $fs->mkdir();
            $debugbar->setStorage(new FileStorage($fs->getAbsolutePath()));
        }

        $this->time = $debugbar->getCollector('time');
        $this->messages = $debugbar->getCollector('messages');
        $this->request = $debugbar->getCollector('request');
        $this->response = $debugbar->getCollector('response');
        $this->controller = $debugbar->getCollector('controller');
        $this->exceptions = $debugbar->getCollector('exceptions');
        $this->errors = $debugbar->getCollector('errors');

        set_exception_handler(array($this, 'exceptionHandler'));
        set_error_handler(array($this, 'errorHandler'));
    }

    public function getDebugbar()
    {
        if(self::$debugbar == null)
        {
            self::$debugbar = new \DebugBar\DebugBar();

            self::$debugbar->addCollector(new PhpInfoCollector());
            self::$debugbar->addCollector(new MessagesCollector());
            self::$debugbar->addCollector(new RequestDataCollector());
            self::$debugbar->addCollector(new ControllerCollector());
            self::$debugbar->addCollector(new ResponseCollector());
            self::$debugbar->addCollector(new TimeDataCollector());
            self::$debugbar->addCollector(new MemoryCollector());
            self::$debugbar->addCollector(new ExceptionsCollector());
            self::$debugbar->addCollector(new ErrorCollector());
//            self::$debugbar->addCollector(new PDOCollector(new TraceablePDO()));
//            self::$debugbar->addCollector(new SwiftMailCollector());
        }
        return self::$debugbar;
    }

    private function protectConfig(&$config)
    {
        if(isset($config['password']))
        {
            $config['password'] = "******";
        }
        foreach ($config as & $item)
        {
            if(is_array($item)) $this->protectConfig($item);

        }
        return $config;
    }
    /**
     * @param $instance Controller
     */
    public function beforeControllerRun($instance)
    {
        if(!$this->getDebugbar()->hasCollector('controller')) return;
        $this->controller->setController($instance);
        $this->request->addRequestData('params', $instance->params);
        $config = Bootstrap::getSingleton()->getConfig();
        if(isset($config['database']['database']))
        {
            $db = new DatabaseManager();
        }
        if(!$this->getDebugbar()->hasCollector('config'))
            $this->getDebugbar()->addCollector(new ConfigCollector($this->protectConfig($config)));
        $this->time->startMeasure('controller', 'Controller process time');
    }
    /**
     * @param $instance Controller
     */
    public function afterControllerRun($instance, $response)
    {
        $this->time->startMeasure('run', 'Core run finished');
        $this->response->setResponse($instance->response);
        if(!$this->getDebugbar()->hasCollector('controller')) return;
        $this->time->stopMeasure('controller');

    }

    /**
     * @param $response Response
     */
    public function beforeResponseSend($response)
    {
        if($response->getAjax() && Bootstrap::isDebug())
        {
            if(!$this->stop)
                $this->getDebugbar()->sendDataInHeaders(null, 'phpdebugbar', $this->config
                ['headerSize']?:4096);
        }
    }

    public function onCoreStartUp($time, $init)
    {
        $this->time->addMeasure('Core start up', $time, $init);
        $this->time->addMeasure('Core init', $init, microtime(true));
    }

    /**
     * @param $view View
     */
    public function displayStyle($view)
    {
        $render = $this->getDebugbar()->getJavascriptRenderer();
        if($this->getDebugbar()->getStorage() != null)
        {
            $url = $view->getController()->getLink("GLFramework\\Modules\\Debugbar\\handler");
            $render->setOpenHandlerUrl($url);
        }
        if($this->time->hasStartedMeasure('run'))
            $this->time->stopMeasure('run');
        if(Bootstrap::isDebug())
        {
            if(!$this->stop)
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
            if(!$this->stop)
                echo $render->render();
        }
    }

    public function onPDOCreated(&$pdo)
    {
        if(!($pdo instanceof TraceablePDO) && !$this->getDebugbar()->hasCollector('pdo'))
        {
            $pdo = new TraceablePDO($pdo);
            $this->getDebugbar()->addCollector(new PDOCollector($pdo, $this->time));
        }
    }

    // TODO: Añadir el colector de Twig cuando solucionen el problema de la herencia condicional
    // TODO: see: (https://github.com/maximebf/php-debugbar/issues/330)
    public function onViewCreated(&$twig)
    {
//        if(!$this->getDebugbar()->hasCollector('twig'))
//        {
//            $twig = new TraceableTwigEnvironment($twig, $this->time);
//            $this->getDebugbar()->addCollector(new TwigCollector($twig));
//        }
    }

    public function onMailTransport($mailer)
    {
//        die("OK");
        $m = \Swift_Mailer::newInstance($mailer);
        $this->messages->aggregate(new SwiftLogCollector($m));
        $this->getDebugbar()->addCollector(new SwiftMailCollector($m));
    }

    public function onLog($message,  $level)
    {
        $this->messages->addMessage($message, $level);
    }

    public function errorHandler($errno, $errstr, $errfile, $errline)
    {
        $this->errors->addError(new GeneratedError($errstr, $errno, $errfile, $errline));
    }

    /**
     * @param $throwlable \Throwable
     */
    public function throwlableHandler($throwlable)
    {
        echo $throwlable->getMessage() . " at " . $throwlable->getFile() . ":" . $throwlable->getLine() . "\n";
        echo $throwlable->getTraceAsString();

        $this->errors->addError(new GeneratedError($throwlable->getMessage(), $throwlable->getCode(),
            $throwlable->getFile(), $throwlable->getLine()));
    }

    /**
     * @param $exception \Exception
     */
    public function exceptionHandler($exception)
    {
        if(!$this->getDebugbar()->hasCollector('exceptions')) return;
        if($exception instanceof \Exception)
            $this->exceptions->addException($exception);
        elseif($exception instanceof \Throwable)
            $this->throwlableHandler($exception);
    }

    public function onMessageDisplay($message, $type)
    {
        $this->messages->addMessage($message, $type);
    }

    public static function stop()
    {
         self::$instance->stop = true;
    }

    public static function timer($name, $label = null)
    {
        if(self::$instance && self::$instance->time)
            self::$instance->time->startMeasure($name, $label);
    }

    public static function stopTimer($name)
    {
        if(self::$instance && self::$instance->time && self::$instance->time->hasStartedMeasure($name))
            self::$instance->time->stopMeasure($name);
    }

    public static function getInstance()
    {
        return self::$instance;
    }
}