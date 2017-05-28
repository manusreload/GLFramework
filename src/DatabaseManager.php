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
 * Date: 13/1/16
 * Time: 19:43
 */

namespace GLFramework;

use GLFramework\Cache\Cache;
use GLFramework\Database\Connection;
use GLFramework\Database\MySQLConnection;

/**
 * Class DatabaseManager
 *
 * @package GLFramework
 */
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

    private $config;

    /**
     * DBConnection constructor.
     *
     * @param null $config
     */
    public function __construct($config = null)
    {
        if (!$config) {
            $config = Bootstrap::getSingleton()->getConfig();
        }
        $this->config = $config;
        $this->connect();
    }

    /**
     * TODO
     *
     * @return \mysqli
     */
    public static function isSelected()
    {
        return self::$selected;
    }

    /**
     * TODO
     *
     * @return array|mixed|null
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * TODO
     *
     * @return MySQLConnection
     */
    public function instanceConnector()
    {
        $config = $this->getConfig();
        if (isset($config['database']['connector'])) {
            $connector = $config['database']['connector'];
            return new $connector();
        }
        return new MySQLConnection();
    }

    /**
     * TODO
     *
     * @return bool
     * @throws \Exception
     */
    public function connect()
    {
        if (!self::$connection) {
            $config = $this->getConfig();
            self::$connection = $this->instanceConnector();
            if (self::$connection->connect($config['database']['hostname'], $config['database']['username'],
                $config['database']['password'])
            ) {
                if (self::$connection->select_database($config['database']['database'])) {
                    self::$selected = true;
                    $this->createCache();

                    if (!GL_INSTALL) {
                        $this->checkDatabaseStructure();
                    }
                    return true;
                }
            } else {
                throw new \Exception(sprintf('Can not establish connection to database! {host=%s, user=%s, database=%s}',
                    $config['database']['hostname'], $config['database']['username'], $config['database']['database']));
            }
        }
        return true;
    }

    /**
     * TODO
     *
     * @param $string
     * @return mixed
     */
    public function escape_string($string)
    {
        if (!self::$connection) {
            return $string;
        }

        if ($string === null) {
            return $string;
        }
        return self::$connection->escape_string($string);
    }

    /**
     * TODO
     *
     * @param $result
     * @param $key
     * @param null $duration
     * @return mixed
     */
    public function cache($result, $key, $duration = null)
    {
        if ($key && $this->getCache()) {
            $this->getCache()->set($key, $result, $duration);
        }
        return $result;
    }

    /**
     * TODO
     *
     * @param $result
     * @param $key
     * @return bool
     */
    public function pre_cache(&$result, $key)
    {
        if ($key !== null && $this->getCache() && $this->getCache()->hash($key)) {
            $result = $this->getCache()->get($key);
            return true;
        }
        return false;
    }

    /**
     * TODO
     */
    public function checkDatabaseStructure()
    {

        if (!self::$checked) {
            self::$checked = true;
            $config = $this->getConfig();
            if (!isset($config['database']['ignoreStructure'])) {
                $manager = new DBStructure();
                if ($manager->haveModelChanges()) {
                    //throw new \Exception("Please, update database structure executing /install.php");
                    $manager->executeModelChanges($this);
                }
            }
        }
    }

    /**
     * TODO
     *
     * @param $query
     * @param array $args
     * @param null $cache
     * @param null $duration
     * @return mixed
     * @throws \Exception
     */
    public function select($query, $args = array(), $cache = null, $duration = null)
    {
        if (self::$connection) {
            if ($this->pre_cache($result, $cache)) {
                return $result;
            }
            return $this->cache(self::$connection->select($query, $args, true), $cache, $duration);
        }
        throw new \Exception('Database connection is not open!');
    }

    /**
     * TODO
     *
     * @param $query
     * @param array $args
     * @param null $cache
     * @return mixed
     */
    public function select_first($query, $args = array(), $cache = null)
    {
        $result = $this->select($query, $args, $cache);
        if ($result && count($result) > 0) {
            return $result[0];
        }
        return $result;
    }

    /**
     * TODO
     *
     * @param $query
     * @param array $args
     * @param null $cache
     * @return int|mixed
     */
    public function select_count($query, $args = array(), $cache = null)
    {
        $result = $this->select_first($query, $args, $cache);
        if ($result) {
            return current($result);
        }
        return 0;
    }

    /**
     * TODO
     *
     * @param $query
     * @param array $args
     * @param null $removeCache
     * @return mixed
     * @throws \Exception
     */
    public function exec($query, $args = array(), $removeCache = null)
    {
        if (self::$connection) {
            if ($this->getCache() && $removeCache) {
                $this->getCache()->remove($removeCache);
            }
            return self::$connection->select($query, $args, false);
        }
        throw new \Exception('Database connection is not open!');
    }

    /**
     * TODO
     *
     * @param $query
     * @param array $args
     * @return bool|mixed
     */
    public function insert($query, $args = array())
    {
        if ($this->exec($query, $args)) {
            return $this->getLastInsertId();
        }
        return false;
    }

    /**
     * TODO
     *
     * @return mixed
     */
    public function getLastInsertId()
    {
        return $this->getConnection()->getLastInsertId();
    }

    /**
     * TODO
     *
     * @return mixed
     */
    public function error()
    {
        return $this->getConnection()->getLastError();
    }

    /**
     * TODO
     *
     * @return mixed
     * @deprecated
     */
    public function getLink()
    {
        return self::$link;
    }

    /**
     * TODO
     *
     * @return Connection
     */
    public function getConnection()
    {
        return self::$connection;
    }

    /**
     * TODO
     *
     * @return Cache
     */
    public function getCache()
    {
        return self::$cache;
    }

    /**
     * TODO
     */
    public function reset()
    {
        self::$connection = null;
        self::$selected = false;
    }

    /**
     * TODO
     */
    public function disconnect()
    {
        $this->getConnection()->disconnect();
        $this->reset();
    }

    /**
     * TODO
     */
    private function createCache()
    {
        $config = $this->getConfig();
        if (isset($config['database']['cache'])) {
            $configCache = $config['database']['cache'];
            if (isset($configCache['connector'])) {
                $this->instanceCache($configCache['connector']);
            }
        }
    }

    /**
     * TODO
     *
     * @param $name
     */
    private function instanceCache($name)
    {
        self::$cache = new $name();
        self::$cache->connect($this->getConfig());
    }
}
