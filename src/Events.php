<?php
/**
 *     GLFramework, small web application framework.
 *     Copyright (C) 2016.  Manuel Muñoz Rosa
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation, either version 3 of the License, or
 *     (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU General Public License for more details.
 *
 *     You should have received a copy of the GNU General Public License
 *     along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Created by PhpStorm.
 * User: manus
 * Date: 2/03/16
 * Time: 9:57
 */

namespace GLFramework;

use GLFramework\Event\Event;
use GLFramework\Event\EventResult;
use GLFramework\Module\Module;
use GLFramework\Modules\Debugbar\Debugbar;

/**
 * Class Events
 *
 * @package GLFramework
 */
class Events
{
    private static $instance;
    /**
     * @var Event[][]
     */
    private $handlers = array();
    private $count = 0;

    /**
     * Events constructor.
     */
    public function __construct()
    {
        self::$instance = $this;
    }

    /**
     * TODO
     *
     * @return Events
     */
    public static function getInstance()
    {
        return self::$instance;
    }

    /**
     *
     * Publica un evento al sistema, devuelve
     *      0 si no hay nadie que lo procese,
     *      true si almenos alguien devuelve true
     *      false si todos devuelven false
     *
     * @param $event
     * @param array $args
     * @return bool|int|string
     * @deprecated Usar Events::dispatch
     */
    public static function fire($event, $args = array())
    {
        return self::getInstance()->_fire($event, $args);
    }

    /**
     * Publica un evento al sistema, devuelve
     *      0 si no hay nadie que lo procese,
     *      true si almenos alguien devuelve true
     *      false si todos devuelven false
     *
     * @param $event
     * @param array $args
     * @return EventResult
     */
    public static function dispatch($event, $args = array())
    {
        return self::getInstance()->_dispatch($event, $args);
    }

    /**
     * TODO
     *
     * @param $result
     * @return bool
     */
    public static function anyFalse($result)
    {
        foreach ($result as $item) {
            if ($item === false) {
                return true;
            }
        }
        return false;
    }

    /**
     * TODO
     *
     * @return Module
     */
    public static function getContext()
    {
        global $context;
        return $context;
    }

    /**
     * Se pone a la escucha de un evento, se puede añadir en la configuración del módulo
     *  dentro de *app:listeners*
     *
     * @param $name
     * @param $fn
     * @param array $context
     * @return Event
     */
    public function listen($name, $fn, $context = array())
    {
        $event = new Event($name, $fn, $context);
        if (!isset($this->handlers[$name])) {
            $this->handlers[$name] = array();
        }
        $this->handlers[$name][] = $event;// array('fn' => $fn, 'context' => $context);
        return $event;
    }

    /**
     * TODO
     *
     * @param $event
     * @param array $args
     * @return array|bool|int
     */
    public function _fire($event, $args = array())
    {
        Log::d("Event " . $event, array('events'));
        global $context;
        $buffer = array();
        if (!is_array($args)) {
            $args = array($args);
        }
        if (isset($this->handlers[$event]) && count($this->handlers[$event]) > 0) {
            $handlers = $this->handlers[$event];
            foreach ($handlers as $item) {
                $fn = $item['fn'];
                $context = $item['context'];
                //                print_debug($item);
                if (is_callable($fn)) {
                    $result = call_user_func_array($fn, $args);
                    if ($result === true) {
                        return true;
                    }
                    $buffer[] = $result;
                } else {
                    //                    print_r($fn);
                    //                    die();
                    $message = '<pre>Can not call event: ' . $event . ', context: ' . get_class($context)
                        . ' function: ' . function_dump($fn) . ' ' . print_r($item, true);
                    Log::getInstance()->error($message, array('events'));
                }
            }
            return $buffer;
        }
        return 0;
    }

    /**
     * TODO
     *
     * @param $event
     * @param array $args
     * @return EventResult
     */
    public function _dispatch($event, $args = array())
    {
        Log::d("Event " . $event, array('events'));
        $eventResult = new EventResult();
        global $context;
        $buffer = array();
        if (!is_array($args)) {
            $args = array($args);
        }
        if (isset($this->handlers[$event]) && count($this->handlers[$event]) > 0) {
            $handlers = $this->handlers[$event];
            foreach ($handlers as $item) {
                $eventResult->addHandler($item);
                $result = $item->run($args);
                if($result !== false) {
                    $eventResult->addResult($result);
                }
            }
        }
        return $eventResult;
    }
}
