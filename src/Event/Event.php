<?php
/**
 * Created by PhpStorm.
 * User: mmunoz
 * Date: 14/06/17
 * Time: 15:50
 */

namespace GLFramework\Event;


use GLFramework\Log;
use GLFramework\Modules\Debugbar\Debugbar;

class Event
{

    private $fn;
    private $context;
    private $name;

    private $module;
    private $definition;

    /**
     * Event constructor.
     * @param $name
     * @param $fn
     * @param $context
     */
    public function __construct($name, $fn, $context)
    {
        $this->name = $name;
        $this->fn = $fn;
        $this->context = $context;
    }


    /**
     * @param array $args
     * @return bool|mixed
     */
    public function run($args = array())
    {
        $result = false;
        global $context;
        $key = "event" . microtime(true) . $this->name;
        $context = $this->context;
        $tag = $this->module . " " . $this->definition;
        Debugbar::timer($key, $this->name . " " . $tag);
        if (is_callable($this->fn)) {
            $result = call_user_func_array($this->fn, $args);
        } else {
            Log::getInstance()
               ->error('Can not call event: ' . $this->name . ' from: ' . $this->module . " " . $this->definition, array('events'));
        }
        Debugbar::stopTimer($key);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @param mixed $module
     */
    public function setModule($module)
    {
        $this->module = $module;
    }

    /**
     * @return mixed
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * @param mixed $definition
     */
    public function setDefinition($definition)
    {
        $this->definition = $definition;
    }

    /**
     * @return mixed
     */
    public function getContext()
    {
        return $this->context;
    }








}