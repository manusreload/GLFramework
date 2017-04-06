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


use GLFramework\Module\Module;

class Events
{
    private static $instance;
    private $handlers = array();

    /**
     * Events constructor.
     */
    public function __construct()
    {
        self::$instance = $this;
    }

    /**
     * @return Events
     */
    public static function getInstance()
    {
        return self::$instance;
    }

    /**
     * Se pone a la escucha de un evento, se puede añadir en la configuración del módulo
     *  dentro de *app:listeners*
     * @param $event
     * @param $fn
     * @param array $context
     */
    public function listen($event, $fn, $context = array())
    {
        if(!isset($this->handlers[$event]))
        {
            $this->handlers[$event] = array();
        }
        $this->handlers[$event][] = array('fn' => $fn, 'context' => $context);

    }

    /**
     *
     * Publica un evento al sistema, devuelve
     *      0 si no hay nadie que lo procese,
     *      true si almenos alguien devuelve true
     *      false si todos devuelven false
     * @param $event
     * @param array $args
     * @return bool|int|string
     * @deprecated Usar Events::dispatch
     */
    public static function fire($event, $args = array())
    {
        return self::getInstance()->_fire($event, $args);
    }

    public function _fire($event, $args = array())
    {
        global $context;
        $buffer = array();
        if(!is_array($args)) $args = array($args);
        if(isset($this->handlers[$event]) && count(($this->handlers[$event])) > 0)
        {
            $handlers = $this->handlers[$event];
            foreach($handlers as $item)
            {
                $fn = $item['fn'];
                $context = $item['context'];
                if(is_callable($fn))
                {
                    $result = call_user_func_array($fn, $args);
                    if($result === true)
                    {
                        return true;
                    }
                    else
                    {
                        $buffer[] = $result;
                    }
                }
                else
                {
//                    print_r($fn);
//                    die();
                    Log::getInstance()->error("<pre>Can not call event: " . $event . ", context: " . get_class($context) .  " function: " . function_dump($fn) . " " . print_r($item, true), array('events'));
                }
            }
            return $buffer;
        }
        return 0;
    }


    /**
     * Publica un evento al sistema, devuelve
     *      0 si no hay nadie que lo procese,
     *      true si almenos alguien devuelve true
     *      false si todos devuelven false
     * @param $event
     * @param array $args
     * @return Event
     */
    public static function dispatch($event, $args = array())
    {
        return self::getInstance()->_dispatch($event, $args);


    }

    public function _dispatch($event, $args = array())
    {
        $eventResult = new Event();
        global $context;
        $buffer = array();
        if(!is_array($args)) $args = array($args);
        if(isset($this->handlers[$event]) && count(($this->handlers[$event])) > 0)
        {
            $handlers = $this->handlers[$event];
            foreach($handlers as $item)
            {
                $eventResult->addHandler($item);
                $fn = $item['fn'];
                $context = $item['context'];
                if(is_callable($fn))
                {
                    $result = call_user_func_array($fn, $args);
                    $eventResult->addResult($result);
                    if($result === true)
                    {
                        return true;
                    }
                    else
                    {
                        $buffer[] = $result;
                    }
                }
                else
                {
                    Log::getInstance()->error("Can not call event: " . $event . " function: " . function_dump($fn), array('events'));
                }
            }
            return $buffer;
        }
        return 0;
    }

    public static function anyFalse($result)
    {
        foreach ($result as $item)
            if($item === false) return true;
        return false;
    }

    /**
     * @return Module
     */
    public static function getContext()
    {
        global $context;
        return $context;
    }
}