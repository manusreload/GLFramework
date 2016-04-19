<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 13/1/16
 * Time: 19:43
 */

namespace GLFramework;


use GLFramework\Cache\Cache;
use GLFramework\Cache\MemoryCache;
use GLFramework\Database\Connection;
use GLFramework\Database\MySQLConnection;

class DatabaseManager
{


    /**
     * @var \mysqli
     */
//    private static $link;
    private static $selected;
    private static $checked = false;
    /**
     * @var Connection
     */
    private static $connection;

    /**
     * @var Cache
     */
    private static $cache;

    /**
     * DBConnection constructor.
     */
    public function __construct()
    {

        $this->connect();
    }

    public function getConfig()
    {
        return Bootstrap::getSingleton()->getConfig();
    }

    /**
     * @return Connection
     */
    public function instanceConnector()
    {
        $config = $this->getConfig();
        if(isset($config['database']['connector']))
        {
            $connector = $config['database']['connector'];
            return new $connector();
        }
        return new MySQLConnection();
    }

    private function createCache()
    {
        $config = $this->getConfig();
        if(isset($config['database']['cache']))
        {
            $configCache = $config['database']['cache'];
            if(isset($configCache['connector']))
            {
                $this->instanceCache($configCache['connector']);
            }
        }
    }
    private function instanceCache($name)
    {
        self::$cache = new $name();
        self::$cache->connect($this->getConfig());
    }

    public function connect()
    {
        if (!self::$connection) {
            $config = $this->getConfig();
            self::$connection = $this->instanceConnector();
            if(self::$connection->connect($config['database']['hostname'], $config['database']['username'], $config['database']['password']))
            {
                if(self::$connection->select_database($config['database']['database']))
                {
                    self::$selected = true;
                    $this->createCache();
                    return true;
                }
            }
            else
            {
                throw new \Exception("Can not establish connection to database!");
            }
        }
        return self::$selected;
    }

    public static function isSelected()
    {
        return self::$selected;
    }

    public function escape_string($string)
    {
        if(!self::$connection) return $string;

        if ($string == null) return $string;
        return self::$connection->escape_string($string);
    }

    public function cache($result, $key, $duration = null)
    {
        if($key && $this->getCache())
        {
            $this->getCache()->set($key, $result, $duration);
        }
        return $result;
    }
    public function pre_cache(&$result, $key)
    {
        if($key != null && $this->getCache())
        {
            if($this->getCache()->hash($key))
            {
                $result = $this->getCache()->get($key);
                return true;
            }
        }
        return false;
    }

    public function checkDatabaseStructure()
    {

        if(!self::$checked)
        {
            self::$checked = true;
            $config = $this->getConfig();
            if(!isset($config['database']['ignoreStructure']))
            {
                $manager = new DBStructure();
                if($manager->haveModelChanges())
                {
                    throw new \Exception("Please, update database structure executing /install.php");
                }
            }
        }
    }
    public function select($query, $cache = null, $duration = null)
    {

        if (self::$connection) {
            if($this->pre_cache($result, $cache)) return $result;
            return $this->cache(self::$connection->select($query, true), $cache, $duration);
        } else {
            throw new \Exception("Database connection is not open!");
        }
    }

    public function select_first($query, $cache = null)
    {
        $result = $this->select($query, $cache);
        if ($result && count($result) > 0) return $result[0];
        return $result;
    }

    public function select_count($query, $cache = null)
    {
        $result = $this->select_first($query, $cache);
        if ($result) return current($result);
        return 0;
    }

    public function exec($query, $removeCache = null)
    {
        if (self::$connection) {
            if($this->getCache() && $removeCache) $this->getCache()->remove($removeCache);
            return self::$connection->select($query, false);
        } else {
            throw new \Exception("Database connection is not open!");
        }
        throw new \Exception("Database connection is not open!");

    }


    public function insert($query)
    {
        if($this->exec($query))
            return $this->getLastInsertId();
        return false;

    }

    public function getLastInsertId()
    {
        return $this->getConnection()->getLastInsertId();
    }

    public function error()
    {
        return $this->getConnection()->getLastError();
    }

    /**
     * @return mixed
     * @deprecated
     */
    public function getLink()
    {
        return self::$link;
    }
    public function getConnection()
    {
        return self::$connection;
    }

    /**
     * @return Cache
     */
    public function getCache()
    {
        return self::$cache;
    }

}