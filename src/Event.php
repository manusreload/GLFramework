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

    private $result;

    /**
     * Event constructor.
     * @param $result
     */
    public function __construct($result)
    {
        $this->result = $result;
    }

    public function isTrue()
    {
        return $this->result === true;
    }

    public function anyFalse()
    {
        foreach ($this->result as $item)
            if($item === false) return true;
        return false;
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


}