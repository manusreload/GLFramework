<?php
/**
 * Created by PhpStorm.
 * User: mmuno
 * Date: 28/09/2016
 * Time: 12:33
 */

namespace GLFramework;


class Event
{

    private $result = false;
    private $handlers = array();
    private $count = 0;

    /**
     * Event constructor.
     * @param $result
     */
    public function __construct($result = null)
    {
        $this->result = $result;
    }

    public function isTrue()
    {
        return $this->result === true;
    }

    public function anyFalse()
    {
        if(is_array($this->result))
        {
            foreach ($this->result as $item)
                if($item === false) return true;
        }
        else
        {
            return $this->result === false;
        }
        return false;
    }

    public function allFalse()
    {
        if(is_array($this->result))
        {
            foreach ($this->result as $item)
                if($item === true) return false;

            return true;
        }
        else
        {
            return $this->result === false;
        }
    }

    public function anyTrue()
    {
        if(is_array($this->result))
        {
            foreach ($this->result as $item)
                if($item === true) return true;
        }
        else
        {
            return $this->result === true;
        }
        return false;
    }

    public function allTrue()
    {
        if(is_array($this->result))
        {
            foreach ($this->result as $item)
                if($item === false) return false;

            return true;
        }
        else
        {
            return $this->result === true;
        }
    }

    public function getString()
    {
        return $this->__toString();
    }

    function __toString()
    {
        // TODO: Implement __toString() method.
        return implode("", $this->result);
    }

    /**
     * @return array
     */
    public function getHandlers()
    {
        return $this->handlers;
    }

    /**
     * @param array $handler
     */
    public function addHandler($handler)
    {
        $this->handlers[] = $handler;
    }

    public function addResult($item)
    {
//        if(!$this->result)
//        {
//            $this->result = $item;
//        }
//        else if(!is_array($this->result))
//        {
//            $this->result = array($this->result, $item);
//        }
//        else
//        {
//        }

        $this->result[] = $item;
        $this->count ++;
    }

    public function getArray()
    {
        if(!$this->result) return array(); // No hay manipuladores
        if(!is_array($this->result)) return array($this->result); // El resultado es solo 1, generar un array
        return $this->result; //
    }
}