<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 28/03/16
 * Time: 13:15
 */

namespace GLFramework\Cache;


class MemoryCache extends Cache
{

    private $array = array();
    public function connect($config = array())
    {
        // TODO: Implement connect() method.
        return true;
    }

    public function set($key, $value, $duration = null)
    {
        // TODO: Implement set() method.
        $this->array[$key] = $value;
    }

    public function get($key)
    {
        // TODO: Implement get() method.
        return $this->array[$key];
    }

    public function hash($key)
    {
        // TODO: Implement hash() method.
        return isset($this->array[$key]);
    }

    public function remove($key)
    {
        // TODO: Implement remove() method.
        unset($this->array[$key]);
    }
}