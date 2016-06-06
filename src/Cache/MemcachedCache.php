<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 28/03/16
 * Time: 13:04
 */

namespace GLFramework\Cache;


class MemcachedCache extends Cache
{

    /**
     * @var \Memcache
     */
    private $conn;
    private $connected = false;
    public function set($key, $value, $duration = null)
    {
        // TODO: Implement set() method.
        if($this->connected)
        {
            return $this->conn->set($key, $value, null, $duration);
        }
    }

    public function get($key)
    {
        // TODO: Implement get() method.
        if($this->connected)
        {
            return $this->conn->get($key);
        }
    }

    public function hash($key)
    {
        // TODO: Implement hash() method.
        if($this->connected)
        {
            return $this->get($key) != null;
        }
        return false;
    }

    public function remove($key)
    {
        // TODO: Implement remove() method.
        if($this->connected)
        {
            return $this->conn->delete($key);
        }
    }

    public function connect($config = array())
    {
        // TODO: Implement connect() method.
        if(isset($config['database']['cache']))
        {
            $configCache = $config['database']['cache'];
            $this->conn = new \Memcache();
            if($this->conn->connect($configCache['host'], $configCache['port'], $configCache['timeout']))
            {
                $this->connected = true;
                return true;
            }
        }
        return false;
    }
}